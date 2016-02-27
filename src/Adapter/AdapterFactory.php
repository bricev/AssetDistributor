<?php

namespace Libcast\AssetDistributor\Adapter;

use Libcast\AssetDistributor\Owner;

class AdapterFactory
{
    /**
     *
     * @param string $vendor
     * @param Owner  $owner
     * @param string $configurationPath
     * @return Adapter
     * @throws \Exception
     */
    public static function build($vendor, Owner $owner, $configurationPath)
    {
        $class = self::getClassName($vendor);

        return new $class($owner, $configurationPath);
    }

    /**
     *
     * @param string $vendor
     * @return string Adapter class name
     * @throws \Exception
     */
    public static function getClassName($vendor)
    {
        $class = sprintf('\Libcast\AssetDistributor\%1$s\%1$sAdapter', $vendor);
        if (!class_exists($class)) {
            throw new \Exception("Adapter '$class' does not exists");
        }

        return $class;
    }
}
