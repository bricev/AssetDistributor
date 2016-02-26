<?php

namespace Libcast\AssetDistributor\Adapter;

use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Configuration\ConfigurationFactory;
use Libcast\AssetDistributor\Owner;

class AdapterCollection extends \ArrayIterator implements Adapter
{
    /**
     *
     * @param string $key
     * @param Adapter $adapter
     * @throws \Exception
     */
    public function offsetSet($key, $adapter)
    {
        if (!$adapter instanceof Adapter) {
            throw new \Exception('AdapterCollection can only contain Adapter objects');
        }

        parent::offsetSet($key, $adapter);
    }

    /**
     *
     * @param Asset $asset
     */
    public function upload(Asset $asset)
    {
        foreach ($this as $adapter) { /** @var Adapter $adapter */
            $adapter->upload($asset);
        }
    }

    /**
     *
     * @param Asset $asset
     */
    public function update(Asset $asset)
    {
        foreach ($this as $adapter) { /** @var Adapter $adapter */
            $adapter->update($asset);
        }
    }

    /**
     *
     * @param Asset $asset
     */
    public function remove(Asset $asset)
    {
        foreach ($this as $adapter) { /** @var Adapter $adapter */
            $adapter->remove($asset);
        }
    }

    /**
     *
     * @param Owner  $owner
     * @param string $configurationPath
     * @return AdapterCollection
     * @throws \Exception
     */
    public static function retrieveFromCache(Owner $owner, $configurationPath)
    {
        $collection = new self;

        if (!$accounts = $owner->getAccounts()) {
            return $collection;
        }

        foreach ($accounts as $vendor => $credentials) {
            $collection[] = AdapterFactory::build($vendor, $owner, $configurationPath);
        }

        return $collection;
    }

    /**
     *
     * @param Asset  $asset
     * @param Owner  $owner
     * @param string $configurationPath
     * @return AdapterCollection
     * @throws \Exception
     */
    public static function buildForAsset(Asset $asset, Owner $owner, $configurationPath)
    {
        $collection = new self;

        $vendors = ConfigurationFactory::getVendors($configurationPath);
        foreach ($vendors as $vendor) {
            $class = AdapterFactory::getClassName($vendor);

            if (!$class::support($asset)) {
                continue;
            }

            $collection[] = new $class($owner, $configurationPath);
        }

        return $collection;
    }
}
