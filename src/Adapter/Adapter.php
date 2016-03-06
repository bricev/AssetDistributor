<?php

namespace Libcast\AssetDistributor\Adapter;

use Libcast\AssetDistributor\Asset\Asset;

interface Adapter
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
     * @return bool
     */
    public function isAuthenticated();

    /**
     *
     * @return void
     */
    public function authenticate();

    /**
     *
     * @param Asset $asset
     * @return bool
     */
    public static function support(Asset $asset);

    /**
     *
     * @param Asset $asset
     */
    public function upload(Asset $asset);

    /**
     *
     * @param Asset $asset
     */
    public function update(Asset $asset);

    /**
     *
     * @param Asset $asset
     */
    public function remove(Asset $asset);
}
