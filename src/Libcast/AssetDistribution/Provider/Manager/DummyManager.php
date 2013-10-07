<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Provider\Manager;

use Libcast\AssetDistribution\Provider\Manager\ManagerInterface;
use Libcast\AssetDistribution\Provider\Manager\AbstractManager;

class DummyManager extends AbstractManager implements ManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function find($key = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function upload()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
    }
}