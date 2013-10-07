<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Asset;

use Libcast\AssetDistribution\Asset\AbstractAsset;
use Libcast\AssetDistribution\Asset\AssetInterface;
use Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface;
use Libcast\AssetDistribution\Provider\Credentials\DummyCredentials;

class ImageAsset extends AbstractAsset implements AssetInterface
{
    /**
     * {@inheritdoc}
     */
    public function addCredentials(CredentialsInterface $credentials) {
        switch (true) {
            case $credentials instanceof DummyCredentials:
                // those credentials are compliant with this asset file type
                // the switch loop is broken to let parent method add credentials
                break;

            default:
                // those credentials are related to a non compliant provider
                // they are not associated with the asset
                // instead we just return the current instance of asset
                return $this;
        }

        return parent::addCredentials($credentials);
    }
}