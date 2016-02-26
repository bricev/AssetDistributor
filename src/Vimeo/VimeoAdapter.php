<?php

namespace Libcast\AssetDistributor\Vimeo;

use Libcast\AssetDistributor\Adapter\AbstractAdapter;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\Video;
use Symfony\Component\HttpFoundation\Request;
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
        }

        return $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $client = $this->getClient();
        $request = Request::createFromGlobals();
        $session = $request->getSession();

        if ($code = $request->query->get('code')) {
            if (!$requestStale = $request->query->get('stale')) {
                throw new \Exception('Missing stale from Vimeo request');
            }

            if (!$sessionStale = $session->get('stale')) {
                throw new \Exception('Missing stale from Vimeo session');
            }

            if (strval($requestStale) !== strval($sessionStale)) {
                throw new \Exception('Vimeo session stale and request stale don\'t match');
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
