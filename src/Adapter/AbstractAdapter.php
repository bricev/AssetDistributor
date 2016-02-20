<?php

namespace Libcast\AssetDistributor\Adapter;

use Doctrine\Common\Cache\Cache;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Driver\Driver;

abstract class AbstractAdapter
{
    /**
     *
     * @var Driver
     */
    protected $driver;

    /**
     *
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     *
     * @param $client
     * @param Cache $cache
     */
    function __construct(Driver $driver, Cache $cache = null)
    {
        $this->driver = $driver;
        $this->cache = $cache;
    }

    /**
     *
     * @return string
     * @throws \Exception
     */
    public function getVendor()
    {
        return $this->driver->getVendor();
    }

    /**
     *
     * @return mixed
     */
    protected function getClient()
    {
        return $this->driver->getClient();
    }

    /**
     * Maps an Asset to a Provider resource identifier
     *
     * @param Asset $asset
     * @param $identifier
     */
    protected function remember(Asset $asset, $identifier)
    {
        if (!$map = $this->cache->fetch((string) $asset)) {
            $map = [];
        }

        $map[$this->getVendor()] = $identifier;

        $this->cache->save((string) $asset, $map);
    }

    /**
     * Returns the Provider identifier of an Asset if exists, or `false` otherwise
     *
     * @param Asset $asset
     * @return string|null
     */
    protected function retrieve(Asset $asset)
    {
        if (!$map = $this->cache->fetch((string) $asset)) {
            return null;
        }

        return isset($map[$this->getVendor()]) ? $map[$this->getVendor()] : null;
    }

    /**
     * Remove an Asset from the map
     *
     * @param Asset $asset
     * @param $identifier
     */
    protected function forget(Asset $asset)
    {
        if (!$map = $this->cache->fetch((string) $asset)) {
            return;
        }

        if (isset($map[$this->getVendor()])) {
            unset($map[$this->getVendor()]);
        }

        $this->cache->save((string) $asset, $map);
    }

    public static function build(Cache $cache)
    {

    }
}
