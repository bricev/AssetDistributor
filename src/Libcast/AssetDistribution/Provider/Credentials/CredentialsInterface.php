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

interface CredentialsInterface
{
    /**
     * Authenticate the application on behalf of a user.
     * 
     * @return boolean False is authentication fails, true otherwise
     */
    public function authenticate();
}