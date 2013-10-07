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

use Libcast\AssetDistribution\Provider\AbstractProvider;
use Libcast\AssetDistribution\Provider\ProviderInterface;

class DummyProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure() 
    {
        $this->setName('dummy');
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return true;
    }
}