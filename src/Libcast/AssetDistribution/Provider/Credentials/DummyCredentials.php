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

use Libcast\AssetDistribution\Provider\Credentials\AbstractCredentials;
use Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface;

class DummyCredentials extends AbstractCredentials implements CredentialsInterface
{
    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        return true;
    }
}