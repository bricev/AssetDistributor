<?php

namespace Libcast\AssetDistributor\Configuration;

class ConfigurationFactory
{
    /**
     *
     * @param $vendor
     * @param $path
     * @return Configuration
     * @throws \Exception
     */
    public static function build($vendor, $path)
    {
        if (!$configuration = parse_ini_file($path)) {
            throw new \Exception("File '$path' is not a valid PHP configuration file");
        }

        if (!in_array($vendor, array_keys($configuration))) {
            throw new \Exception("Missing $vendor configuration");
        }

        $class = sprintf('\Libcast\AssetDistributor\%s\%sConfiguration', $vendor);
        if (!class_exists($class)) {
            throw new \Exception("Missing configuration class for $vendor");
        }

        return $class($configuration[$vendor]);
    }

    /**
     *
     * @param $path
     * @return array
     * @throws \Exception
     */
    public static function getVendors($path)
    {
        if (!$configuration = parse_ini_file($path)) {
            throw new \Exception("File '$path' is not a valid PHP configuration file");
        }

        return array_keys($configuration);
    }
}