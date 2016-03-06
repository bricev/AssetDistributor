<?php

namespace Libcast\AssetDistributor\Configuration;

use Libcast\AssetDistributor\LoggerTrait;
use Libcast\AssetDistributor\Request;
use Psr\Log\LoggerInterface;

abstract class AbstractConfiguration
{
    use LoggerTrait;

    /**
     *
     * @var array
     */
    protected $configuration = [];

    /**
     *
     * @param array $configuration
     */
    public function __construct(array $configuration, LoggerInterface $logger = null)
    {
        if (!isset($configuration['redirectUri'])) {
            $configuration['redirectUri'] = $this->getCurrentUri();
        }

        $this->configuration = $configuration;
        $this->logger = $logger;
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
        $request = Request::get();

        $uri = sprintf('%s://%s%s',
            $request->getScheme(),
            $request->getHttpHost(),
            $request->getBaseUrl());

        $this->debug('Build current URI', ['uri' => $uri]);

        return filter_var($uri, FILTER_SANITIZE_URL);
    }
}
