<?php

namespace Libcast\AssetDistributor;

class Configuration
{
    /**
     *
     * @var array
     */
    protected $configuration = [];

    /**
     *
     * @param $path
     * @throws \Exception
     */
    function __construct($path)
    {
        if (!$this->configuration = parse_ini_file($path)) {
            throw new \Exception("File '$path' is not a valid PHP configuration file");
        }
    }

    public function from($vendor)
    {
        if (!in_array($vendor, array_keys($this->configuration))) {
            throw new \Exception("Missing $vendor configuration");
        }

        return $this->configuration[$vendor];
    }
}
