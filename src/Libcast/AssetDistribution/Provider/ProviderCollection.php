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

use Libcast\AssetDistribution\Provider\ProviderInterface;

/**
 * Store Providers into a collection.
 * 
 * ProviderCollection objects can be accessed/traversed as an array.
 * Eg. $collection = new ProviderCollection;
 *     $collection[] = new YoutubeProviders($parameters);
 *     $collection['youtube'] = new YoutubeProviders($parameters);
 * 
 * ProviderCollection objects are serializable so that a collection can 
 * easilly be stored in a database for future usage.
 */
class ProviderCollection implements \ArrayAccess, \Iterator, \Serializable
{
    /**
     * 
     * @var array List of providers
     */
    private $providers = array();

    /**
     * 
     * @var array List of provider IDs
     */
    private $provider_ids = array();

    /**
     * \ArrayAccess::offsetSet
     */
    public function offsetSet($offset, $provider) 
    {
        if (!$provider instanceof ProviderInterface) {
            throw new \Exception('Value must be an instance of ProviderInterface');
        }

        if (in_array($id = $provider->getId(), $this->provider_ids)) {
            throw new \Exception("There is already a provider with '$id' as identifier.");
        }

        if (is_null($offset)) {
            $this->providers[] = $provider;
        } else {
            $this->providers[$offset] = $provider;
        }
        
        $this->provider_ids[] = $provider->getId();
    }

    /**
     * \ArrayAccess::offsetExists
     */
    public function offsetExists($offset) 
    {
        return isset($this->providers[$offset]);
    }

    /**
     * \ArrayAccess::offsetUnset
     */
    public function offsetUnset($offset) 
    {
        unset($this->providers[$offset]);
    }

    /**
     * \ArrayAccess::offsetGet
     */
    public function offsetGet($offset) 
    {
        return isset($this->providers[$offset]) ? $this->providers[$offset] : null;
    }

    /**
     * \Iterator::rewind
     */
    function rewind()
    {
        return reset($this->providers);
    }

    /**
     * \Iterator::current
     */
    function current()
    {
        return current($this->providers);
    }

    /**
     * \Iterator::key
     */
    function key()
    {
        return key($this->providers);
    }

    /**
     * \Iterator::next
     */
    function next()
    {
        return next($this->providers);
    }

    /**
     * \Iterator::valid
     */
    function valid()
    {
        return null !== $this->key() && false !== $this->key();
    }

    /**
     * \Serializable::serialize
     */
    public function serialize()
    {
        $data = array();

        foreach ($this->providers as $key => $provider) {
            $data[$key] = serialize($provider);
        }

        return serialize($data);
    }

    /**
     * \Serializable::unserialize
     */
    public function unserialize($data)
    {
        if (!is_array($providers = unserialize($data))) {
            throw new \Exception('Error while unserializing data.');
        }

        foreach ($providers as $key => $provider) {
            $this->offsetSet($key, unserialize($provider));
        }
    }

    /**
     * Credentials proxy method
     *
     * @return \Libcast\AssetDistribution\Provider\ProviderCollection
     */
    public function authenticate()
    {
        foreach ($this->providers as $provider) {
            if (!$provider->isAuthorized()) {
                $provider->authenticate();
                $provider->unauthenticate();
            }
        }

        return $this;
    }

    /**
     * Try to execute method on providers
     * 
     * @param type $method
     * @param type $arguments
     * @return \Libcast\AssetDistribution\Provider\ProviderCollection
     */
    public function __call($method, $arguments)
    {
        // only proxy methods for provider configuration
        if (!in_array($method, array(
            'setSetting', 'setSettings', 'getSetting', 'getSettings', 'deleteSetting', 'hasParameter',
            'setParameter', 'setParameters', 'getParameter', 'getParameters', 'deleteParameter', 'hasParameter',
            'loadConfiguration',
            'setLogger', 'setSession',
        ))) {
            return;
        }

        foreach ($this->providers as $provider) {
            if (method_exists($provider, $method)) {
                call_user_func_array(array($provider, $method), $arguments);
            }
        }

        return $this;
    }

    public function __toString() {
        return $this->serialize();
    }
}