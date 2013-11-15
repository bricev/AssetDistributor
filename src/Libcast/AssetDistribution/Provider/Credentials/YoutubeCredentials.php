<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Provider\Credentials;

use Libcast\AssetDistribution\Provider\Credentials\AbstractCredentials;
use Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface;
use Libcast\AssetDistribution\Request\CurlRequest;
use Libcast\AssetDistribution\Request\HttpRequest;

class YoutubeCredentials extends AbstractCredentials implements CredentialsInterface
{
    const STATUS_APPROVED        = 'approved';

    const STATUS_FIRST_LOGIN     = 'first_login';

    const STATUS_LOGIN_REFRESHED = 'refreshed';

    const STATUS_LOGIN_SESSION   = 'session_login';

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $provider = $this->getProvider(); /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $session = $provider->getSession(); /* @var $session \Libcast\AssetDistribution\Session\Session */

        switch (true) {
            case $access_token = $this->getAccessToken():
                // application has been authorized

                $provider->log('Youtube authentication: already logged', $access_token);

                break;

            case isset($_GET['error']):
                // application has been refused authentication

                $provider->log('Youtube authentication: an error occured', $_GET['error'], 'error');

                $this->setStatus(self::STATUS_ERROR);

                return false;

            case isset($_GET['code']) 
                && !$session->retrieve('yt_code', $_GET['code'])
                && $code = $_GET['code']:
                // usually executed from callback url
                // application need to exchange a code against an access_token

                $provider->log('Youtube authentication: auth code received', $code);

                $request = new CurlRequest($provider->getLogger());
                $request->setUrl($provider->getSetting('token_url'))
                        ->setArguments(array(
                            'code'          => $code,
                            'client_id'     => $provider->getSetting('client_id'),
                            'client_secret' => $provider->getSetting('client_secret'),
                            'redirect_uri'  => $provider->getSetting('redirect_uri'),
                            'grant_type'    => 'authorization_code',
                        ))
                        ->post();

                $response = json_decode($request->getResponse(), true);

                if (isset($response['error'])) {
                    $this->setError($response['error']);

                    return false;
                }

                // in case of a first user login, a refresh_token is returned
                // this one should be saved in a database to auto log user next time
                if (isset($response['refresh_token'])) {
                    $provider->log('YouTube refresh_token', $response['refresh_token']);

                    $this->setRefreshToken($response['refresh_token']);

                    $this->setStatus(self::STATUS_FIRST_LOGIN);
                } else {
                    $this->setStatus(self::STATUS_LOGGED_IN);
                }

                // if no access_token then the authentication failed
                if (!isset($response['access_token'])) {
                    return false;
                }

                // make sur validation code will not be validated again for another
                // asset's provider
                $session->store('yt_code', $code);

                $this->setAccessToken(
                        $response['access_token'], 
                        isset($response['expires_in']) ? $response['expires_in'] : 3600);

                // backup the provider to help recover settings & params from session
                // with the refresh_token
                $provider->backup();

                break;

            case $refresh_token = $this->getRefreshToken():
                // use refresh_token to get a new access_token without user login

                $provider->log('Youtube authentication: refresh auth', $refresh_token);

                $request = new CurlRequest($provider->getLogger());
                $request->setUrl($provider->getSetting('token_url'))
                        ->setArguments(array(
                            'client_id'     => $provider->getSetting('client_id'),
                            'client_secret' => $provider->getSetting('client_secret'),
                            'refresh_token' => $refresh_token,
                            'grant_type'    => 'refresh_token',
                        ))
                        ->post();

                $response = json_decode($request->getResponse(), true);

                if (isset($response['error'])) {
                    $this->setError($response['error']);

                    $provider->log('Youtube refresh token: an error occured', $response['error'], 'error');

                    // no break: ask for user authorization again
                } elseif (isset($response['access_token'])) {
                    $this->setAccessToken(
                            $response['access_token'], 
                            isset($response['expires_in']) ? $response['expires_in'] : 3600);

                    $this->setStatus(self::STATUS_LOGIN_REFRESHED);

                    // backup the provider to help recover settings & params from session
                    // with the refresh_token
                    $provider->backup();

                    break;
                }

            default :
                // redirect user to Google authentication form

                $provider->log('Youtube authentication: get validation from Youtube', array('unauthenticated'));

                // backup the provider to help recover settings & params from session
                // after redirection
                $provider->backup();

                $request = new HttpRequest($provider->getLogger());
                $request->setUrl($provider->getSetting('authorize_url'), array(
                            $provider->getSetting('scope'),
                            $provider->getSetting('client_id'),
                            $provider->getSetting('redirect_uri'),
                            $provider->hasParameter('state') ? 
                                $provider->getParameter('state') : 
                                $provider->getId(),
                        ))
                        ->redirect();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unauthenticate()
    {
        $provider = $this->getProvider(); /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $provider->deleteSetting('access_token')
                ->deleteSetting('access_token_expiration')
                ->backup();

        $this->setStatus(self::STATUS_LOGGED_OUT);

        $request = new HttpRequest($provider->getLogger());
        $request->setUrl($provider->getSetting('logout_url'), $provider->getSetting('redirect_uri'))
                ->redirect(true);
    }

    /**
     * {@inheritdoc}
     */
    public function revoke()
    {
        $provider = $this->getProvider(); /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        // send revoke request to Google
        if ($token = $this->getAccessToken()) {
            $request = new CurlRequest($provider->getLogger());
            $request->setUrl($provider->getSetting('revoke_url'), $token)
                    ->get();
        }

        $provider->deleteParameter('refresh_token')
                ->deleteSetting('access_token')
                ->deleteSetting('access_token_expiration')
                ->backup();
    }

    /**
     * Store Google's oAuth access_token in both the class attribute and a PHP 
     * session so that it can be used again later by the component.
     * 
     * @param  string   $token       API access_token
     * @param  integer  $expires_in  Number of seconds before token expires
     */
    protected function setAccessToken($token, $expires_in = 3600)
    {
        $provider = $this->getProvider(); 
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $provider->setSetting('access_token', $token);

        $provider->setSetting('access_token_expiration', time() + (int) $expires_in);

        $provider->log("New access_token has been created for provider '$provider'", array(
            $provider->getSetting('access_token'),
            $provider->getSetting('access_token_expiration'),
        ));
    }

    /**
     * Return the access_token if exists or `null` otherwise.
     * 
     * @return string|null API access_token
     */
    public function getAccessToken()
    {
        $provider = $this->getProvider(); 
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $token = $provider->hasSetting('access_token') ? 
                $provider->getSetting('access_token') : 
                null;

        if (!$token) {
            $provider->log("Impossible to find access_token from '$provider' settings");

            return null;
        }

        $expiration = $provider->hasSetting('access_token_expiration') ? 
                $provider->getSetting('access_token_expiration') : 
                null;

        if ($expiration && $expiration <= time()) {
            // token expired

            $provider->log('The token expired', array(
                $token,
                date('Y-m-d H:i:s', $expiration),
                date('Y-m-d H:i:s'),
            ));

            $this->revoke();

            return null;
        }

        $provider->log("Get access_token for provider '$provider'", $token);

        return $token;
    }

    /**
     * Store Google's oAuth refresh_token in both the class attribute and a PHP 
     * session so that it can be used again later by the component.
     * 
     * @param string $token
     */
    protected function setRefreshToken($token)
    {
        $provider = $this->getProvider(); 
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $provider->setParameter('refresh_token', $token);

        $provider->log("New refresh_token has been created for provider '$provider'", array(
            $provider->getParameter('refresh_token'),
        ));
    }

    /**
     * Return the access_token if exists or `null` otherwise.
     * 
     * @return string|null API refresh_token
     */
    public function getRefreshToken()
    {
        $provider = $this->getProvider();
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $token = $provider->hasParameter('refresh_token') ? 
                $provider->getParameter('refresh_token') : 
                null;

        $provider->log("Get refresh_token from '$provider' parametters", $token ? $token : 'NULL');

        return $token;
    }
}