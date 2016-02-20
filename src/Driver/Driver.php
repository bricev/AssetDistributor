<?php

namespace Libcast\AssetDistributor\Driver;

interface Driver
{
    /**
     *
     * @return string
     */
    public function getVendor();

    /**
     *
     * @return mixed
     */
    public function getClient();

    /**
     *
     * @return void
     */
    public function authenticate();
}
