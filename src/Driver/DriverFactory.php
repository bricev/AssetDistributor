<?php

namespace Libcast\AssetDistributor\Driver;

use Libcast\AssetDistributor\Configuration;
use Libcast\AssetDistributor\Owner;

class DriverFactory
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
        $class = sprintf('\Libcast\AssetDistributor\%1$s\%1$sDriver', $vendor);
        if (!class_exists($class)) {
            throw new \Exception("Driver '$class' does not exists");
        }

        $credentials = null;
        if ($accounts = $owner->getAccounts()) {
            $credentials = isset($accounts[$vendor]) ? $accounts[$vendor] : null;
        }

        return $class($configuration->from($vendor), $credentials);
    }
}
