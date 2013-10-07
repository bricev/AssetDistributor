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

use Libcast\AssetDistribution\Provider\ProviderInterface;
use Libcast\AssetDistribution\Provider\ProviderCollection;

abstract class AbstractAsset implements \Serializable
{
    const VISIBILITY_VISIBLE = 'visible';
    const VISIBILITY_HIDDEN  = 'hidden';
    const VISIBILITY_PRIVATE = 'private';

    /**
     * 
     * @var string File path
     */
    protected $path;

    /**
     * 
     * @var array List of providers
     */
    protected $providers = array();

    /**
     * 
     * @var array Parameters
     */
    protected $parameters = array();

    /**
     * 
     * @var string visible|hidden|private
     */
    protected $visibility;

    /**
     * 
     * @var boolean True if the asset has not been saved at all
     */
    protected $is_new = true;

    /**
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Manage a digital asset across providers.
     * 
     * @param  string                       $path      File path
     * @param  Provider|ProviderCollection  $provider  Provider(s) to manage to file with.
     * @param  LoggerInterface              $logger    Psr logger
     * @throws \Exception
     */
    public function __construct($path = null, &$providers = null, \Psr\Log\LoggerInterface $logger = null) 
    {
        $this->setPath($path);

        if ($providers instanceof ProviderInterface) {
            // affiliate the single provider to the asset
            $this->addProvider($providers);
        } elseif ($providers instanceof ProviderCollection) {
            // affiliate each provider from the collection to the asset
            $this->addProviderCollection($providers);
        } elseif (!is_null($providers)) {
            // a non provider-related object can't be associated with asset
            throw new \Exception('Only Provider or ProviderCollection objects may be associated.');
        }

        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * 
     * @param string $path
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function setPath($path)
    {
        if ($path && (!file_exists($path) || !is_readable($path))) {
            throw new \Exception("File '$path' is not readable.");
        }

        $this->path = $path;

        return $this;
    }

    /**
     * 
     * @return string File path
     * @throws \Exception
     */
    public function getPath()
    {
        if (!$this->path) {
            throw new \Exception('There is no file attached to this asset.');
        }

        return $this->path;
    }

    /**
     * Add the provider only if it can handle the asset's file format.
     * 
     * @param ProviderInterface $provider
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function addProvider(ProviderInterface $provider) 
    {
        $this->providers[$provider->getId()] = $provider;

        return $this;
    }

    /**
     * Remove the provider
     * 
     * @param ProviderInterface $provider
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function removeProvider(ProviderInterface $provider) 
    {
        if (array_key_exists($provider->getId(), $this->providers)) {
            unset($this->providers[$provider->getId()]);
        }

        return $this;
    }

    /**
     * Add each provider of a collection
     * 
     * @param ProviderCollection $collection
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function addProviderCollection(ProviderCollection $collection)
    {
        foreach ($collection as $provider) {
            $this->addProvider($provider);
        }
        
        return $this;
    }

    /**
     * 
     * @param mixed $provider A ProviderInterface object or identifier
     * @return boolean True if exists
     */
    public function hasProvider($provider)
    {
        return isset($this->providers[$provider instanceof ProviderInterface ? $provider->getId() : (string) $provider]);
    }

    /**
     * 
     * @param null|string $id Provider identifier
     * @return \Libcast\AssetDistribution\Provider\ProviderInterface
     */
    public function getProvider($id)
    {
        if (!$this->hasProvider($id)) {
            throw new \Exception("This asset is not affiliated to '$id' provider.");
        }

        return $this->providers[$id];
    }

    /**
     * 
     * @return array List of providers
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * 
     * @param string $name
     * @param string $value
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * 
     * @param array $parameters
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function setParameters($parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * 
     * @param string $name
     * @return string The value of parameter named $name
     */
    public function getParameter($name)
    {
        if (!isset($this->parameters[$name]))
        {
            throw new \Exception("Parameter '$name' does not exists.");
        }

        return $this->parameters[$name];
    }

    /**
     * 
     * @return array List of parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * 
     * @return bool True if param $name exists
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * 
     * @param string $visibility
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     * @throws \Exception
     */
    public function setVisibility($visibility)
    {
        if (!in_array($visibility, self::getVisibilities())) {
            throw new \Exception("Visibility '$visibility' is not valid.");
        }

        $this->visibility = $visibility;

        return $this;
    }

    /**
     * 
     * @return string visible|hidden|private
     */
    public function getVisibility()
    {
        if (!$this->visibility) {
            $this->setVisibility(self::VISIBILITY_VISIBLE);
        }

        return $this->visibility;
    }

    /**
     * 
     * @return boolean True if the asset has not been saved at all
     */
    public function isNew()
    {
        return $this->isNew();
    }

    /**
     * 
     * @param boolean $new True if the asset has not been saved at all
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function setIsNew($new = false)
    {
        $this->is_new = (boolean) $new;

        return $this;
    }

    /**
     * 
     * @return array List of visibility labels
     */
    public static function getVisibilities()
    {
        return array(
            self::VISIBILITY_VISIBLE,
            self::VISIBILITY_HIDDEN,
            self::VISIBILITY_PRIVATE,
        );
    }

    /**
     * 
     * @return array List of common field names
     */
    public static function getCommonFields()
    {
        return array(
            'title',
            'description',
            'keywords',
            'category',
            'shareable',
            'downloadable',
        );
    }

    /**
     * 
     * @param LoggerInterface $logger
     * @return \Libcast\AssetDistribution\Provider\ProviderInterface
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * \Serializable::serialize 
     */
    public function serialize()
    {
        // wrap all providers into a collection
        $collection = new ProviderCollection;
        foreach ($this->getProviders() as $provider) {
            $collection[] = $provider;
        }

        return serialize(array(
            'path'         => $this->path,
            'parameters'   => $this->getParameters(),
            'visibility'   => $this->getVisibility(),
        ));
    }

    /**
     * \Serializable::unserialize 
     * 
     * @param string $data Serialized data
     */
    public function unserialize($data)
    {
        $unserialized = unserialize($data);

        $this->setPath($unserialized['path']);

        $this->setParameters($unserialized['parameters']);

        $this->setVisibility($unserialized['visibility']);
    }

    /**
     * Logger proxy method.
     * 
     * @param string $message
     * @param mixed  $context
     * @param mixed  $level
     */
    public function log($message, $context = array(), $level = 'debug')
    {
        if (!is_array($context)) {
            $context = (array) $context;
        }

        if ($logger = $this->logger) {
            $logger->$level($message, $context);
        }
    }

    /**
     * 
     * @return array List of manager methods
     */
    protected function getManagerMethods()
    {
        $methods = array();
        $reflect = new \ReflectionClass('\Libcast\AssetDistribution\Provider\Manager\ManagerInterface');
        foreach ($reflect->getMethods() as $method) {
            /* @var $method \ReflectionMethod */
            $methods[] = $method->getName();
        }

        return $methods;
    }

    /**
     * Call methods from all available managers – composite style.
     * 
     * @param string $method Method called
     * @param array $arguments List of aguments to pass to the method
     * @return void 
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        // verify that de demanded method can be properly executed by the manager
        if (!in_array($method, $this->getManagerMethods())) {
            throw new \Exception("Method '$method' can't be called on this object.");
        }

        $n = 0;
        $error = array();
        foreach ($this->getProviders() as $provider) {
            $manager = $provider->getManager();
            $manager->setAsset($this);

            try {
                $this->log("Call '$method' method on a {$provider->getName()} manager.", $arguments);

                $return = call_user_func_array(array($manager, $method), $arguments);

                $this->addProvider($provider); // this updates the provider

                $n++;

                return $return;
            } catch (\Exception $e) {
                $this->log("Error calling '$method' method on a {$provider->getName()} manager.", $e, 'error');

                $error[] = $e->getMessage();
            }   
        }

        if ($error) {
            throw new \Exception(implode(PHP_EOL, $error));
        }

        if (!$n) {
            throw new \Exception("There is no provider on which to apply '$method'.");
        }
    }

    public function __toString()
    {
        return $this->serialize();
    }
}