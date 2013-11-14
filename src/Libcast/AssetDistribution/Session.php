<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class Session
{
    const SAVED_OBJECTS = 'assetdistribution_saved_objects';

    /**
     *
     * @var object Local storage session
     */
    protected $session;

    private function __construct($session = null) 
    {
        $this->setSession($session);
    }

    /**
     * Get session instance - singleton style
     * 
     * @staticvar null $instance
     * @return \static
     */
    public static function getInstance($session = null)
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new static($session);
        }

        return $instance;
    }

    /**
     * 
     * @param mixed $session
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     */
    protected function setSession($session = null)
    {
        if (!$this->session) {
            $this->session = $session ? $session : new SymfonySession;
        }

        return $this;
    }

    /**
     * 
     * @return object Session for local storage
     */
    protected function getSession()
    {
        if (!$this->session) {
            $this->setSession();
        }

        if ($this->session instanceof Session 
                && session_status() !== PHP_SESSION_ACTIVE
                && !$this->session->isStarted()) {
            $this->session->start();
        }

        return $this->session;
    }

    /**
     * 
     * @param string $type
     * @param string $name
     * @param mixed $value
     */
    public function store($type, $name, $value = true)
    {
        $session = $this->getSession(); /* @var $session SymfonySession */

        $objects = $session->get(self::SAVED_OBJECTS, array());

        if (!isset($objects[$type])) {
            $objects[$type] = array();
        }

        $objects[$type][(string) $name] = $value;

        $session->set(self::SAVED_OBJECTS, $objects);
    }

    /**
     * 
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public function retrieve($type, $name)
    {
        $session = $this->getSession(); /* @var $session SymfonySession */

        $objects = $session->get(self::SAVED_OBJECTS, array());

        return isset($objects[$type][(string) $name]) ? $objects[$type][(string) $name] : null;
    }

    /**
     * 
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public function delete($type, $name = null)
    {
        $session = $this->getSession(); /* @var $session SymfonySession */

        $objects = $session->get(self::SAVED_OBJECTS, array());

        if (!isset($objects[$type])) {
            return;
        }

        if (!$name) {
            // delete the whole object container
            unset($objects[$type]);
        } else {
            // only delete a specific object 

            if (!isset($objects[$type][(string) $name])) {
                return;
            }

            unset($objects[$type][(string) $name]);
        }

        $session->set(self::SAVED_OBJECTS, $objects);
    }

    /**
     * Proxy call session methods 
     * 
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        $session = $this->getSession(); /* @var $session SymfonySession */

        if (method_exists($session, $method)) {
            return call_user_func_array(array($session, $method), $arguments);
        }

        throw new \Exception("Method '$method' does not exists.");
    }
}