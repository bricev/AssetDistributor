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
     * @param  mixed            $settings   List of settings or path to `.ini` config file
     * @param  mixed            $parameters List of parameters or path to `.ini` config file
     * @param  LoggerInterface  $logger     Psr logger
     * @return \Libcast\AssetDistribution\Provider\ProviderInterface
     * @throws \Exception
     */
    public static function load($name, $settings = null, $parameters = null, \Psr\Log\LoggerInterface $logger = null)
    {
        switch ($name) {
            case self::YOUTUBE: 
                return new YoutubeProvider($settings, $parameters, $logger);

            default : 
                throw new \Exception("Provider '$name' is not yet supported.");
        }

        return null;
    }

    /**
     * 
     * @return array Return the list of all supported providers
     */
    public static function getNames()
    {
        return array(
            self::YOUTUBE,
        );
    }
}