<?php

namespace Libcast\AssetDistributor\Dailymotion;

use Dailymotion as DailymotionBase;
use Libcast\AssetDistributor\Request;

class Dailymotion extends DailymotionBase
{
    /**
     * {@inheritdoc}
     */
    public function getAccessToken($forceRefresh = false)
    {
        if ($this->grantType === null) {
            // No grant type defined, the request won't be authenticated
            return null;
        }
        $session = $this->getSession();

        // Check if session is present and if it was created for the same grant type
        // i.e: if the grant type to create the session was `AUTHORIZATION` and the current grant type is
        // `CLIENT_CREDENTIALS`, we don't want to call the API on behalf of another user.
        if (!empty($session) && isset($session['grant_type']) && ((int)$session['grant_type'] === $this->grantType)) {
            if (!$forceRefresh && isset($session['access_token'])) {
                if (!isset($session['expires']) || (time() < $session['expires'])) {
                    return $session['access_token'];
                }
                // else: Token expired
            }
            // No valid access token found, try to refresh it
            if (isset($session['refresh_token'])) {
                $grantType = $session['grant_type'];
                $session = $this->oauthTokenRequest(array(
                        'grant_type' => 'refresh_token',
                        'client_id' => $this->grantInfo['key'],
                        'client_secret' => $this->grantInfo['secret'],
                        'scope' => implode(chr(32), $this->grantInfo['scope']),
                        'refresh_token' => $session['refresh_token'],
                ));
                $session['grant_type'] = $grantType;
                $this->setSession($session);
                return $session['access_token'];
            }
        }
        try {
            if ($this->grantType === self::GRANT_TYPE_AUTHORIZATION) {
                // Use Request singleton object to avoid getting values from another Adapter
                $request = Request::get();
                $code = $request->query->get('code');

                $error = filter_input(INPUT_GET, 'error');

                if (!empty($code)) {
                    // We've been called back by authorization server
                    $session = $this->oauthTokenRequest(array(
                            'grant_type' => 'authorization_code',
                            'client_id' => $this->grantInfo['key'],
                            'client_secret' => $this->grantInfo['secret'],
                            'scope' => implode(chr(32), $this->grantInfo['scope']),
                            'redirect_uri' => $this->grantInfo['redirect_uri'],
                            'code' => $code,
                    ));
                    $session['grant_type'] = $this->grantType;
                    $this->setSession($session);
                    return $session['access_token'];
                } elseif (!empty($error)) {
                    $message = filter_input(INPUT_GET, 'error_description');
                    if ($error === 'access_denied') {
                        $e = new \DailymotionAuthRefusedException($message);
                    } else {
                        $e = new \DailymotionAuthException($message);
                    }
                    $e->error = $error;
                    throw $e;
                } else {
                    // Ask the client to request end-user authorization
                    throw new \DailymotionAuthRequiredException();
                }
            } elseif ($this->grantType === self::GRANT_TYPE_CLIENT_CREDENTIALS) {
                $session = $this->oauthTokenRequest(array(
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->grantInfo['key'],
                        'client_secret' => $this->grantInfo['secret'],
                        'scope' => implode(chr(32), $this->grantInfo['scope']),
                ));
                $session['grant_type'] = $this->grantType;
                $this->setSession($session);
                return $session['access_token'];
            } elseif ($this->grantType === self::GRANT_TYPE_PASSWORD) {
                if (!isset($this->grantInfo['username']) || !isset($this->grantInfo['password'])) {
                    // Ask the client to request end-user credentials
                    throw new \DailymotionAuthRequiredException();
                }
                $session = $this->oauthTokenRequest(array(
                        'grant_type' => 'password',
                        'client_id' => $this->grantInfo['key'],
                        'client_secret' => $this->grantInfo['secret'],
                        'scope' => implode(chr(32), $this->grantInfo['scope']),
                        'username' => $this->grantInfo['username'],
                        'password' => $this->grantInfo['password'],
                ));
                $session['grant_type'] = $this->grantType;
                $this->setSession($session);
                return $session['access_token'];
            }
        } catch (\DailymotionAuthException $e) {
            // clear session on error
            $this->clearSession();
            throw $e;
        }

        return null;
    }
}
