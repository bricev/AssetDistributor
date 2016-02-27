<?php

namespace Libcast\AssetDistributor\Configuration;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractConfiguration
{
    /**
     *
     * @var array
     */
    protected $configuration = [];

    /**
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        if (!isset($configuration['redirectUri'])) {
            $configuration['redirectUri'] = $this->getCurrentUri();
        }

        $this->configuration = $configuration;
    }

    /**
     *
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->configuration[$key]) ? $this->configuration[$key] : $default;
    }

    /**
     *
     * @return array
     */
    public function getCategoryMap()
    {
        return [];
    }

    /**
     *
     * @return mixed
     */
    protected function getCurrentUri()
    {
        $request = Request::createFromGlobals();

        $url = sprintf('%s://%s%s',
            $request->getScheme(),
            $request->getHttpHost(),
            $request->getBaseUrl());

        return filter_var($url, FILTER_SANITIZE_URL);
    }
}
