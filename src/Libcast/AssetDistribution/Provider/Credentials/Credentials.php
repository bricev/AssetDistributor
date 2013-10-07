<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Provider\Credentials;

use Libcast\AssetDistribution\Provider\ProviderInterface;
use Libcast\AssetDistribution\Provider\YoutubeProvider;
use Libcast\AssetDistribution\Provider\Credentials\YoutubeCredentials;

class Credentials
{
    /**
     * Load credentials - factory style.
     * 
     * @param  ProviderInterface  $provider
     * @param  array              $parameters
     * @return \Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface
     * @throws \Exception
     */
    public static function load(ProviderInterface $provider, $parameters = array())
    {
        switch (true) {
            case $provider instanceof YoutubeProvider: 
                return new YoutubeCredentials($provider, $parameters);

            default : 
                try {
                    $name = $provider->getName();
                } catch (\Exception $e) {
                    $name = 'unknown';
                }

                throw new \Exception("Provider '$name' is not yet supported.");
        }

        return null;
    }
}