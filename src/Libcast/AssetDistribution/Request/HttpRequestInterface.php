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

interface HttpRequestInterface
{
    /**
     * Execute a GET request.
     * 
     * @return  \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function get();

    /**
     * Execute a POST request.
     * 
     * @return  \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function post();

    /**
     * Execute a PUT request.
     * 
     * @return  \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function put();

    /**
     * Execute a DELETE request.
     * 
     * @return  \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function delete();
}