<?php

namespace Libcast\AssetDistributor\Vimeo;

use Libcast\AssetDistributor\Driver\AbstractDriver;
use Libcast\AssetDistributor\Driver\Driver;
use Symfony\Component\HttpFoundation\Request;
use Vimeo\Vimeo;

class VimeoDriver extends AbstractDriver implements Driver
{
    /**
     *
     * @var Vimeo
     */
    protected $client;

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
        } else {
            $this->authenticate();
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

            $request = $client->accessToken($code, $this->redirectUri);

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
            $scopes = ['create', 'edit', 'delete', 'upload'];

            $this->redirect($client->buildAuthorizationEndpoint($this->redirectUri, $scopes, $state));
        }

        $this->isAuthenticated = true;
    }
}
