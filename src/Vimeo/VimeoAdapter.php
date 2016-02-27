<?php

namespace Libcast\AssetDistributor\Vimeo;

use Libcast\AssetDistributor\Adapter\AbstractAdapter;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Vimeo\Vimeo;

class VimeoAdapter extends AbstractAdapter implements Adapter
{
    /**
     *
     * @return string
     * @throws \Exception
     */
    public function getVendor()
    {
        return 'Vimeo';
    }

    /**
     *
     * @return Vimeo
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new Vimeo(
            $this->getConfiguration('id'),
            $this->getConfiguration('secret')
        );

        if ($token = $this->getCredentials()) {
            $client->setToken($token);
            $this->isAuthenticated = true;
        }

        $this->client = $client; // Now getClient() returns \Vimeo\Vimeo

        if (!$this->isAuthenticated()) {
            $this->authenticate();
        }

        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $client = $this->getClient();
        $request = Request::createFromGlobals();
        $session = new Session;

        if ($code = $request->query->get('code')) {
            if (!$requestState = $request->query->get('state')) {
                throw new \Exception('Missing state from Vimeo request');
            }

            if (!$sessionState = $session->get('state')) {
                throw new \Exception('Missing state from Vimeo session');
            }

            if (strval($requestState) !== strval($sessionState)) {
                throw new \Exception('Vimeo session state and request state don\'t match');
            }

            $request = $client->accessToken($code, $this->getConfiguration('redirectUri'));

            if (200 === $request['status'] and $token = $request['body']['access_token']) {
                $client->setToken($token);
                $this->setCredentials($token);
            } else {
                throw new \Exception('Vimeo authentication failed');
            }
        }

        if (!$client->getToken()) {
            $state = mt_rand();
            $session->set('state', $state);

            $this->redirect($client->buildAuthorizationEndpoint(
                $this->getConfiguration('redirectUri'),
                $this->getConfiguration('scopes'),
                $state
            ));
        }

        $this->isAuthenticated = true;
    }

    /**
     * {@inheritdoc}
     */
    public static function support(Asset $asset)
    {
        if (!$asset instanceof Video) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function upload(Asset $asset)
    {
        if (!self::support($asset)) {
            throw new \Exception('Vimeo adapter only handles video assets');
        }

        if (!is_null($this->retrieve($asset))) {
            $this->update($asset);
            return;
        }

        $uri = $this->getClient()->upload($asset->getPath());

        $this->remember($asset, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Asset $asset)
    {
        /** @todo implement update */
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Asset $asset)
    {
        /** @todo implement remove */
    }
}
