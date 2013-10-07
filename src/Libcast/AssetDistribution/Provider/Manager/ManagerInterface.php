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

interface ManagerInterface
{    
    /**
     * Check if the asset is new or have already been saved on a provider
     * 
     * @return boolean True if the asset is new
     */
    public function isNew();

    /**
     * Remotly search for the asset into the provider
     * 
     * @param string $key A key that can be searched (eg. title, ID...)
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function find($key = null);

    /**
     * Create (upload) or update the asset
     * 
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function save();

    /**
     * Upload the asset's file to the provider
     * 
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     * @throws \Exception
     */
    public function upload();

    /**
     * Update the asset remotly from the provider
     * 
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     * @throws \Exception
     */
    public function update();

    /**
     * Delete the asset remotly from the provider
     */
    public function delete();
}