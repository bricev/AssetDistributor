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
use Libcast\AssetDistribution\Session;

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
     *
     * @var \Libcast\AssetDistribution\Session\Session
     */
    protected $session;

    /**
     * Manage a digital asset across providers.
     *
     * @param  string                       $path      File path
     * @param  Provider|ProviderCollection  $provider  Provider(s) to manage to file with.
     * @param  LoggerInterface              $logger    Psr logger
     * @param  object                       $session   Session manager
     * @throws \Exception
     */
    public function __construct($path = null, $providers = null, \Psr\Log\LoggerInterface $logger = null, $session = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }

        $this->setSession($session);

        $this->setPath($path);

        $this->retrieve();

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
        return $this->isNew;
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
     * @param mixed $session
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    protected function setSession($session = null)
    {
        if (!$this->session) {
            $this->session = Session::getInstance($session);
        }

        return $this;
    }

    /**
     *
     * @return \Libcast\AssetDistribution\Session\Session
     */
    protected function getSession()
    {
        if (!$this->session) {
            $this->setSession();
        }

        return $this->session;
    }

    /**
     * Clean session
     */
    public function cleanSession()
    {
        $this->getSession()->invalidate();
        $this->log('Session renewed');

        foreach ($this->getProviders() as $provider) {
            $provider->backup();
        }
    }

    /**
     *
     * @param LoggerInterface $logger
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Persists asset into session storage
     *
     * @return void
     */
    public function backup()
    {
        $session = $this->getSession();

        $session->store('asset', $this, serialize($this));

        $this->log("Asset '$this' backuped");
    }

    /**
     * If a asset has been persisted into session storage, try to retrieve its
     * settings and parameters to merge them with the current object
     *
     * @return void
     */
    public function retrieve()
    {
        $session = $this->getSession();

        if (!$serialized = $session->retrieve('asset', $this)) {
            $this->log("Asset '$this' was not backuped");
            return;
        }

        $asset = unserialize($serialized);
        if (!$asset instanceof AssetInterface) {
            $this->log("Asset '$this' backup is corrupted", $serialized, 'error');
            return;
        }

        $this->setPath($asset->getPath());
        $this->setParameters($asset->getParameters());
        $this->setVisibility($asset->getVisibility());

        $this->log("Asset '$this' retrieved", array(
            'path'        => $asset->getPath(),
            'parametters' => $asset->getParameters(),
            'visibility'  => $asset->getVisibility(),
        ));
    }

    /**
     * Logger proxy method.
     *
     * @param string $message
     * @param mixed  $context
     * @param mixed  $level
     */
    public function log($message, $context = array(), $level = 'info')
    {
        if (!is_array($context)) {
            $context = (array) $context;
        }

        if ($logger = $this->logger) {
            $logger->$level($message, $context);
        }
    }

    /**
     * \Serializable::serialize
     */
    public function serialize()
    {
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
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        // verify that de demanded method can be properly executed by the manager
        if (!in_array($method, $this->getManagerMethods())) {
            throw new \Exception("Method '$method' can't be called on this object.");
        }

        $session = $this->getSession();

        // backup asset so that, if any provider need to get credentials and has
        // to redirect, asset data will be saved
        $this->backup();

        // manage the asset with each provider's manager
        $n = 0;
        $auth = true;
        foreach ($this->getProviders() as $provider) {
            // whether it works or not, we count how many managers the method has
            // been called on
            $n++;

            // if the asset has already managed by a provider (eg. after a
            // redirection due to authentication) then continue
            if ($session->retrieve("$this/$method", $provider)) {
                $this->log("Provider '$provider' has already called '$method'");
                continue;
            }

            $auth = !$auth ? false : $provider->isAuthorized();

            // connect provider (authenticate)
            $manager = $provider->getManager();
            $manager->setAsset($this)
                    ->connect();

            /* @var $manager \Libcast\AssetDistribution\Provider\Manager\AbstractManager */

            // try to execute methode, ignore errors
            try {
                call_user_func_array(array($manager, $method), $arguments);
            } catch (\Exception $exception) {
                $this->log("Error calling '$method' method on the {$provider->getBrand()} provider '$provider'", $exception, 'error');
            }

            $this->log("Provider '$provider' managed method '$method' successfully", $arguments);

            // list the asset as having been managed by this provider
            $session->store("$this/$method", $provider);

            // backup asset so that, if any provider need to get credentials and has
            // to redirect, asset data will be saved
            $this->backup();

            // disconnect to enable manage on multiple providers of a same
            // origine (same brand)
            $manager->disconnect();
        }

        // clean renew but re-store providers if one of them was not authorized so
        // that credentials can't be lost
        $this->cleanSession();

        if (!$n) {
            throw new \Exception("There is no provider on which to apply '$method'");
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getPath();
    }
}