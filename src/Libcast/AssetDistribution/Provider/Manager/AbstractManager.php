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

use Libcast\AssetDistribution\Provider\ProviderInterface;
use Libcast\AssetDistribution\Asset\AssetInterface;

abstract class AbstractManager
{
    /**
     * 
     * @var \Libcast\AssetDistribution\Provider\ProviderInterface
     */
    protected $provider;

    /**
     * 
     * @var \Libcast\AssetDistribution\Asset\AssetInterface
     */
    protected $asset;

    /**
     * 
     * @var boolean True if the asset has not been saved in the provider
     */
    protected $is_new = true;

    /**
     * 
     * @var boolean True if the asset has been uploaded in the provider
     */
    protected $is_uploaded = false;

    /**
     * 
     * @param ProviderInterface $provider
     * @param AssetInterface $asset
     * @throws \Exception
     */
    public function __construct(ProviderInterface $provider = null, AssetInterface $asset = null) 
    {
        $this->provider = $provider;

        $this->asset = $asset;

        $this->connect();
    }

    /**
     * 
     * @param ProviderInterface $provider
     * @return \Libcast\AssetDistribution\Provider\Manager\ManagerInterface
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * 
     * @return \Libcast\AssetDistribution\Provider\ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * 
     * @param AssetInterface $asset
     * @return \Libcast\AssetDistribution\Provider\Manager\ManagerInterface
     */
    public function setAsset(AssetInterface $asset)
    {
        $this->asset = $asset;

        return $this;
    }

    /**
     * 
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * 
     * @return array List of provider specific parameters from the asset
     */
    protected function getProviderParameters()
    {
        $asset = $this->getAsset(); /* @var $asset \Libcast\AssetDistribution\Asset\AbstractAsset */
        $provider_id = $this->getProvider()->getId();

        if ($asset->hasParameter($provider_id)) {
            $data = $asset->getParameter($provider_id);
        } else {
            $data = array();
        }

        return $data;
    }

    /**
     * 
     * @param  string $name  Parameter's name
     * @param  string $value Parameter's value
     * @return \Libcast\AssetDistribution\Provider\Manager\ManagerInterface
     */
    protected function setProviderParameter($name, $value)
    {
        $data = $this->getProviderParameters();

        $data[$name] = $value;

        $this->getAsset()->setParameter($this->getProvider()->getId(), $data);

        return $this;
    }

    /**
     * 
     * @param  string $name  Parameter's name
     * @return mixed         Parameter's value
     * @throws \Exception
     */
    protected function getProviderParameter($name)
    {
        $data = $this->getProviderParameters();

        if (!isset($data[$name])) {
            throw new \Exception("Parameter '$name' does not exists.");
        }

        return $data[$name];
    }

    /**
     * 
     * @param  string $name  Parameter's name
     * @return boolean       True if the parameter exists, false otherwise
     */
    protected function hasProviderParameter($name)
    {
        return array_key_exists($name, $this->getProviderParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function find()
    {
        return $this->getAsset();
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        if ($this->isNew()) {
            // asset is new, the associated file must be uploaded
            return $this->upload();
        } else {
            // asset already exists on the provider, its data should be updated
            return $this->update();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        return $this->getAsset()->removeProvider($this->getProvider());
    }

    /**
     * Checks if the provider has been authorized to connect the provider on 
     * behalf of a user
     * 
     * @return boolean True if authorized, false otherwise
     */
    public function ping()
    {
        return $this->getProvider()->isAuthorized();
    }

    /**
     * If the provider is not yet authorized, try to authenticate a user
     */
    public function connect()
    {
        if (!$this->ping()) {
            $this->getProvider()->authenticate();
        }
    }
}