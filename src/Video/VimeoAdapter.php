<?php

namespace Libcast\AssetDistributor\Vimeo;

use Libcast\AssetDistributor\Adapter\AbstractAdapter;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\Video;

/**
 *
 * @method \Vimeo\Vimeo getClient()
 */
class VimeoAdapter extends AbstractAdapter implements Adapter
{
    /**
     *
     * @param Video $asset
     * @throws \Exception
     */
    public function upload(Asset $asset)
    {
        if (!$asset instanceof Video) {
            throw new \Exception('Vimeo adapter only handles video assets');
        }

        $uri = $this->getClient()->upload($asset->getPath());

        $this->remember($asset, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Asset $asset)
    {
        /** @todo implement update */
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Asset $asset)
    {
        /** @todo implement remove */
    }
}
