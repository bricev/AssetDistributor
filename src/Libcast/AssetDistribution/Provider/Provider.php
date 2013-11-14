<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Provider;

use Libcast\AssetDistribution\Provider\YoutubeProvider;

class Provider
{
    const YOUTUBE = 'youtube';

    /**
     * Load a provider - factory style.
     * 
     * @param  string           $name        Provider name
     * @param  string           $id          Unique identifier
     * @param  mixed            $settings    List of settings or path to `.ini` config file
     * @param  mixed            $parameters  List of parameters or path to `.ini` config file
     * @param  LoggerInterface  $logger      Psr logger
     * @param  object           $session     Session manager
     * @return \Libcast\AssetDistribution\Provider\ProviderInterface
     * @throws \Exception
     */
    public static function load($name, $id, $settings = null, $parameters = null, \Psr\Log\LoggerInterface $logger = null, $session = null)
    {
        switch ($name) {
            case self::YOUTUBE: 
                return new YoutubeProvider($id, $settings, $parameters, $logger, $session);

            default : 
                throw new \Exception("Provider '$name' is not yet supported.");
        }

        return null;
    }

    /**
     * 
     * @return array Return the list of all supported providers
     */
    public static function getBrands()
    {
        return array(
            self::YOUTUBE,
        );
    }
}