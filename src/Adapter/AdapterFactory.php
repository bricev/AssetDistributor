<?php

namespace Libcast\AssetDistributor\Adapter;

use Libcast\AssetDistributor\Configuration;
use Libcast\AssetDistributor\Driver\DriverFactory;
use Libcast\AssetDistributor\Owner;

class AdapterFactory
{
    /**
     *
     * @param $vendor
     * @param Configuration $configuration
     * @param Owner $owner
     * @return mixed
     * @throws \Exception
     */
    public static function build($vendor, Configuration $configuration, Owner $owner)
    {
        $class = sprintf('\Libcast\AssetDistributor\%1$s\%1$sAdapter', $vendor);
        if (!class_exists($class)) {
            throw new \Exception("Adapter '$class' does not exists");
        }

        $driver = DriverFactory::build($vendor, $configuration, $owner);

        return $class($driver, $owner->getCache());
    }
}
