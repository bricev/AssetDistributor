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

use Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface;

interface AssetInterface
{
    /**
     * Method addCredentials must filter credentials so that only those compliant
     * with the asset's file format are associated
     * 
     * @param CredentialsInterface $credentials
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function addCredentials(CredentialsInterface $credentials);
}