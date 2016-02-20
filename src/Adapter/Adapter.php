<?php

namespace Libcast\AssetDistributor\Adapter;

use Libcast\AssetDistributor\Asset\Asset;

interface Adapter
{
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
