<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Request;

abstract class AbstractRequest
{
    /**
     * 
     * @var string Request URL
     */
    protected $url;

    /**
     * 
     * @var array List of request arguments
     */
    protected $arguments = array();

    /**
     * 
     * @var array Response
     */
    protected $response = array();

    /**
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Generates a Request
     * 
     * @param LoggerInterface $logger     Psr logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * 
     * @param string $url
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * 
     * @return string Request URL
     * @throws \Exception
     */
    protected function getUrl()
    {
        if (!$this->url) {
            throw new \Exception('A URL must be provided to execute a request.');
        }

        return $this->url;
    }

    /**
     * 
     * @param string $name
     * @param string $value
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * 
     * @param array $arguments
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function setArguments($arguments)
    {
        $this->arguments = array_merge($this->arguments, $arguments);

        return $this;
    }

    /**
     * 
     * @param string $name
     * @return string The value of argument named $name
     */
    public function getArgument($name)
    {
        if (!isset($this->arguments[$name]))
        {
            throw new \Exception("Argument '$name' does not exists.");
        }

        return $this->arguments[$name];
    }

    /**
     * 
     * @return array List of arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * 
     * @return bool True if param $name exists
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * 
     * @param string $response
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    protected function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * 
     * @return string Response
     * @throws \Exception
     */
    public function getResponse($return = 'response_body')
    {
        if (!$this->response || !is_array($this->response)) {
            throw new \Exception('The request has not yet been executed.');
        }

        return array_key_exists($return, $this->response) ? $this->response[$return] : $this->response;
    }

    /**
     * 
     * @param LoggerInterface $logger
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
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
}