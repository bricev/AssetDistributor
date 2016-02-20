<?php

namespace Libcast\AssetDistributor\Driver;

use Libcast\AssetDistributor\Owner;

abstract class AbstractDriver
{
    /**
     *
     * @var array
     */
    protected $configuration = [];

    /**
     *
     * @var Owner
     */
    protected $owner;

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
     * @var string
     */
    protected $redirectUri;

    /**
     *
     * @param array $configuration
     * @param mixed $credentials
     */
    function __construct(array $configuration, Owner $owner)
    {
        $this->configuration = $configuration;
        $this->owner = $owner;

        $this->redirectUri = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['PHP_SELF']);
        $this->redirectUri = filter_var($this->redirectUri, FILTER_SANITIZE_URL);

        if (!$this->isAuthenticated) {
            $this->authenticate();
        }
    }

    /**
     *
     * @return string
     */
    public function getConfiguration($key, $default = null)
    {
        return isset($this->configuration[$key]) ? $this->configuration[$key] : $default;
    }

    /**
     *
     * @return string
     */
    abstract protected function getVendor();

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
     * @return void
     */
    abstract protected function authenticate();

    /**
     *
     * @param $url
     * @param bool $from_client
     * @throws \Exception
     */
    public function redirect($url, $from_client = false)
    {
        if ('cli' === php_sapi_name()) {
            throw new \Exception('Impossible to redirect from CLI');
        }

        if ($from_client or headers_sent()) {
            echo sprintf('<noscript><meta http-equiv="refresh" content="0; url=%s" /></noscript><script type="text/javascript">  window.location.href="%s"; </script><a href="%s">%s</a>', $url);
        } else {
            header("Location: $url");
        }

        exit;
    }
}
