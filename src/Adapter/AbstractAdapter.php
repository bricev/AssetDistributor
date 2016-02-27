<?php

namespace Libcast\AssetDistributor\Adapter;

use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Configuration\CategoryRegistry;
use Libcast\AssetDistributor\Configuration\Configuration;
use Libcast\AssetDistributor\Configuration\ConfigurationFactory;
use Libcast\AssetDistributor\Owner;

abstract class AbstractAdapter
{
    /**
     *
     * @var mixed
     */
    protected $client;

    /**
     *
     * @var Owner
     */
    protected $owner;

    /**
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     *
     * @var string
     */
    protected $credentials;

    /**
     *
     * @var bool
     */
    protected $isAuthenticated = false;

    /**
     *
     * @param mixed $configuration
     * @param Owner $owner
     */
    public function __construct(Owner $owner, $configuration)
    {
        $this->owner = $owner;

        if ($configuration instanceof Configuration) {
            $this->configuration = $configuration;
        } else {
            $this->configuration = ConfigurationFactory::build($this->getVendor(), $configuration);
        }

        // Register Vendor shared configurations
        CategoryRegistry::addVendorCategories($this->getVendor(), $this->configuration->getCategoryMap());
    }

    /**
     *
     * @return string
     */
    abstract protected function getVendor();

    /**
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getConfiguration($key, $default = null)
    {
        return $this->configuration->get($key, $default);
    }

    /**
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function getCache()
    {
        return $this->owner->getCache();
    }

    /**
     *
     * @return mixed
     */
    public function getCredentials()
    {
        if ($this->credentials) {
            return $this->credentials;
        }

        if (!$accounts = $this->owner->getAccounts()) {
            return null;
        }

        if (!isset($accounts[$this->getVendor()]) or !$credentials = $accounts[$this->getVendor()]) {
            return null;
        }

        return $this->credentials = $credentials;
    }

    /**
     *
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
        $this->owner->setAccount($this->getVendor(), $credentials);
    }

    /**
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * Maps an Asset to a Provider resource identifier
     *
     * @param Asset $asset
     * @param $identifier
     */
    protected function remember(Asset $asset, $identifier)
    {
        if (!$map = $this->getCache()->fetch((string) $asset)) {
            $map = [];
        }

        $map[$this->getVendor()] = $identifier;

        $this->getCache()->save((string) $asset, $map);
    }

    /**
     * Returns the Provider identifier of an Asset if exists, or `false` otherwise
     *
     * @param Asset $asset
     * @return string|null
     */
    protected function retrieve(Asset $asset)
    {
        if (!$map = $this->getCache()->fetch((string) $asset)) {
            return null;
        }

        return isset($map[$this->getVendor()]) ? $map[$this->getVendor()] : null;
    }

    /**
     * Remove an Asset from the map
     *
     * @param Asset $asset
     */
    protected function forget(Asset $asset)
    {
        if (!$map = $this->getCache()->fetch((string) $asset)) {
            return;
        }

        if (isset($map[$this->getVendor()])) {
            unset($map[$this->getVendor()]);
        }

        $this->getCache()->save((string) $asset, $map);
    }

    /**
     *
     * @param string $url
     * @param bool   $from_client
     * @throws \Exception
     */
    public function redirect($url, $from_client = false)
    {
        if ('cli' === php_sapi_name()) {
            throw new \Exception('Impossible to redirect from CLI');
        }

        if ($from_client or headers_sent()) {
            echo sprintf('<noscript><meta http-equiv="refresh" content="0; url=%1$s" /></noscript><script type="text/javascript">  window.location.href="%1$s"; </script><a href="%1$s">%1$s</a>', $url);
        } else {
            header("Location: $url");
        }

        exit;
    }
}
