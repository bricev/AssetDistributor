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

class ProviderCollection implements \ArrayAccess, \Iterator, \Serializable
{
    /**
     * 
     * @var array List of providers
     */
    private $providers = array();

    /**
     * 
     * @var integer
     */
    private $position = 0;

    /**
     * Store Providers into a collection.
     * 
     * ProviderCollection objects can be accessed/traversed as an array.
     * Eg. $collection = new ProviderCollection;
     *     $collection[] = new YoutubeProviders($parameters);
     * 
     * ProviderCollection objects are serializable so that a collection can 
     * easilly be stored in a database for future usage.
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * \ArrayAccess::offsetSet
     */
    public function offsetSet($offset, $value) 
    {
        if (!$value instanceof ProviderInterface) {
            throw new \Exception('Value must be an instance of ProviderInterface');
        }

        if (is_null($offset)) {
            $this->providers[] = $value;
        } else {
            $this->providers[$offset] = $value;
        }
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
        $this->position = 0;
    }

    /**
     * \Iterator::current
     */
    function current()
    {
        return $this->providers[$this->position];
    }

    /**
     * \Iterator::key
     */
    function key()
    {
        return $this->position;
    }

    /**
     * \Iterator::next
     */
    function next()
    {
        $this->position++;
    }

    /**
     * \Iterator::valid
     */
    function valid()
    {
        return isset($this->providers[$this->position]);
    }

    /**
     * \Serializable::serialize
     */
    public function serialize()
    {
        $data = array();

        foreach ($this->providers as $provider) {
            $data[] = serialize($provider);
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

        foreach ($providers as $provider) {
            $this->offsetSet(null, unserialize($provider));
        }
    }

    public function __toString() {
        return $this->serialize();
    }
}