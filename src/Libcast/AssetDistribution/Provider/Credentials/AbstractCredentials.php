<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Provider\Credentials;

use Libcast\AssetDistribution\Provider\ProviderInterface;

abstract class AbstractCredentials
{
    const STATUS_LOGGED_OUT = 'logged_out';

    const STATUS_LOGGED_IN  = 'logged_in';

    const STATUS_ERROR      = 'error';

    /**
     * 
     * @var \Libcast\AssetDistribution\Provider\ProviderInterface
     */
    protected $provider;

    /**
     * Note: credentials can bring their own statuses.
     * 
     * @var string logged_out|logged_in|error|*
     */
    protected $status = 'logged_out';

    /**
     * 
     * @var string Error message
     */
    protected $error;

    /**
     * Initiate credentials with parameters.
     * 
     * Method `authenticate` has to be called to actually create a valid session.
     * 
     * @param  ProviderInterface  $provider    A provider
     * @param  array              $parameters  List of authentication parameters
     */
    public function __construct(ProviderInterface $provider = null, array $parameters = array())
    {
        $this->provider = $provider;
    }

    /**
     * 
     * @param ProviderInterface $provider
     * @return \Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface
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
     * @param string $status
     * @return \Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface
     */
    protected function setStatus($status)
    {
        $this->status = $status;

        $this->getProvider()->log('Set credentials status', $this->status);

        return $this;
    }

    /**
     * 
     * @return string logged_out|logged|error|*
     */
    public function getStatus()
    {
        if (!$this->status)
        {
            $this->status = self::STATUS_LOGGED_OUT;
        }

        $this->getProvider()->log('Get credentials status', $this->status);

        return $this->status;
    }

    /**
     * 
     * @param string $error
     * @return \Libcast\AssetDistribution\Provider\Credentials\CredentialsInterface
     */
    protected function setError($error)
    {
        $this->setStatus(self::STATUS_ERROR);

        $this->error = $error;

        $this->getProvider()->log($error, $this, 'error');

        return $this;
    }

    /**
     * 
     * @return string An error message (null if none)
     */
    public function getError()
    {
        return $this->error;
    }
}