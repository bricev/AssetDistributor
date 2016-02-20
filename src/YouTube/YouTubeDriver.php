<?php

namespace Libcast\AssetDistributor\YouTube;

use Libcast\AssetDistributor\Driver\AbstractDriver;
use Libcast\AssetDistributor\Driver\Driver;
use Symfony\Component\HttpFoundation\Request;

class YouTubeDriver extends AbstractDriver implements Driver
{
    /**
     *
     * @var \Google_Service_YouTube
     */
    protected $client;

    /**
     *
     * @var string Json
     */
    protected $accessToken;

    /**
     *
     * @var string
     */
    protected $redirectUri;

    /**
     *
     * @return string
     * @throws \Exception
     */
    public function getVendor()
    {
        return 'YouTube';
    }

    /**
     *
     * @return \Google_Service_YouTube
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $google = new \Google_Client;
        $google->setClientId($this->getConfiguration('id'));
        $google->setClientSecret($this->getConfiguration('secret'));
        $google->setApplicationName($this->getConfiguration('application'));
        $google->setAccessType('offline');

        $google->setScopes([
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/youtube.upload',
        ]);

        $google->setRedirectUri($this->redirectUri);

        if ($token = $this->getCredentials()) {
            $google->setAccessToken($token);

            if ($google->isAccessTokenExpired()) {
                $json = json_decode($token);
                $google->refreshToken($json->refresh_token);
                $this->setCredentials($google->getAccessToken());
            }
        }

        return $this->client = new \Google_Service_YouTube($google);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $google = $this->getClient()->getClient();
        $request = Request::createFromGlobals();
        $session = $request->getSession();

        if ($code = $request->query->get('code')) {
            if (!$requestStale = $request->query->get('stale')) {
                throw new \Exception('Missing stale from YouTube request');
            }

            if (!$sessionStale = $session->get('stale')) {
                throw new \Exception('Missing stale from YouTube session');
            }

            if (strval($requestStale) !== strval($sessionStale)) {
                throw new \Exception('YouTube session stale and request stale don\'t match');
            }

            $google->authenticate($code);

            $session->set('token', $google->getAccessToken());
//            $this->redirect($this->redirectUri);
        }

        if ($token = $session->get('token')) {
            $google->setAccessToken($token);
        }

        if ($accessToken = $google->getAccessToken()) {
            $this->accessToken = $accessToken;
            $this->setCredentials($accessToken);
        } else {
            $state = mt_rand();
            $google->setState($state);
            $session->set('state', $state);

            $this->redirect($google->createAuthUrl());
        }

        $this->isAuthenticated = true;
    }
}
