<?php

namespace Libcast\AssetDistributor\Configuration;

interface Configuration
{
    /**
     *
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     *
     * @return array
     */
    public function getCategoryMap();
}
