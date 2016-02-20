<?php

namespace Libcast\AssetDistributor;

use Doctrine\Common\Cache\Cache;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Adapter\AdapterCollection;
use Libcast\AssetDistributor\Asset\Asset;

class Owner
{
    /**
     *
     * @var string
     */
    protected $identifier;

    /**
     *
     * @var AdapterCollection
     */
    protected $adapters;

    /**
     * 
     * @var Cache
     */
    protected $cache;

    /**
     *
     * @param $identifier
     * @param Cache $cache
     * @param array $adapters
     */
    function __construct($identifier, Cache $cache = null, array $adapters = [])
    {
        $this->identifier = $identifier;
        $this->adapters = new AdapterCollection($adapters);
        $this->cache = $cache;
    }

    /**
     *
     * @param AdapterCollection $adapters
     */
    public function setAdapters(AdapterCollection $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     *
     * @param Adapter $adapter
     */
    public function addAdapter(Adapter $adapter)
    {
        $this->adapters[] = $adapter;
    }

    /**
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Upload Asset on all Adapters
     *
     * @param Asset $asset
     */
    public function upload(Asset $asset)
    {
        $this->adapters->upload($asset);
    }

    /**
     * Edit Asset on all Adapters
     *
     * @param Asset $asset
     */
    public function update(Asset $asset)
    {
        $this->adapters->update($asset);
    }

    /**
     * Remove Asset on all Adapters
     *
     * @param Asset $asset
     */
    public function remove(Asset $asset)
    {
        $this->adapters->remove($asset);
    }

    /**
     *
     * @return array
     */
    public function getAccounts()
    {
        if (!$this->cache) {
            return [];
        }

        return $this->cache->contains($this->identifier) ? $this->cache->fetch($this->identifier) : [];
    }

    /**
     *
     * @param $vendor
     * @param $credentials
     */
    public function setAccount($vendor, $credentials)
    {
        if (!$accounts = $this->getAccounts()) {
            return;
        }

        $accounts[$vendor] = $credentials;

        $this->cache->save($this->identifier, $accounts);
    }

    /**
     *
     * @param $identifier
     * @param Configuration $configuration
     * @param Cache $cache
     * @return Owner
     */
    public static function retrieveFromCache($identifier, Configuration $configuration, Cache $cache)
    {
        $owner = new self($identifier, $cache);

        if ($adapters = AdapterCollection::retrieveFromCache($owner, $configuration)) {
            $owner->setAdapters($adapters);
        }

        return $owner;
    }
}
