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

use Libcast\AssetDistribution\Request\HttpRequestInterface;
use Libcast\AssetDistribution\Request\HttpRequest;

class CurlRequest extends HttpRequest implements HttpRequestInterface
{
    const USER_AGENT = 'Libcast/AssetDistribution v1.0';

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->curl(self::METHOD_GET);
    }

    /**
     * {@inheritdoc}
     */
    public function post()
    {
        return $this->curl(self::METHOD_POST);
    }

    /**
     * {@inheritdoc}
     */
    public function put()
    {
        return $this->curl(self::METHOD_PUT);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        return $this->curl(self::METHOD_DELETE);
    }

    /**
     * Execute a cURL request and return all data from the repsonse.
     * 
     * @param   string $method get|post|put|delete
     * @return  \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function curl($method)
    {
        $url       = $this->getUrl();
        $body      = $this->getBody();
        $arguments = $this->getArguments();
        $headers   = $this->getHeaders();

        $this->log('Sending cURL request', array($method, $url));

        if (!in_array($method, self::getMethods())) {
            throw new \Exception("Method '$method' is not yet supported.");
        }

        $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,   $method);
                curl_setopt($ch, CURLOPT_HTTPAUTH,        CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  false);
                curl_setopt($ch, CURLOPT_VERBOSE,         true);
                curl_setopt($ch, CURLOPT_FAILONERROR,     false);
                curl_setopt($ch, CURLOPT_HEADER,          true);
                curl_setopt($ch, CURLOPT_USERAGENT,       self::USER_AGENT);

        if ($headers) {
            $this->log('Adding headers to cURL request', $headers);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($body && is_string($body) && 0 === strpos($body, '@')) {
            // $body is a string beginning with `@`
            // this value prefix is reserved for file transfert
            // eg. `@/path/to/local/file`
            $path = substr($body, 1);
            if (!$path || !file_exists($path) || !is_readable($path)) {
                throw new \Exception("File '$path' is not readable.");
            }

            // currently, only PUT requets support file transfert
            if (self::METHOD_PUT !== $method) {
                throw new \Exception("Method '$method' is not supported for file transfert.");
            }

            $this->log('Attaching file to cURL request', $path);

            // collect file
            $file = fopen($path, 'rb');

            // include file content to the request
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, $file);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($path));
        } elseif ($body) {
            // add a body to the request

            $this->log('Adding a body to cURL request', $body);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } elseif ($arguments) {
            // add one or many arguments to the request

            $this->log('Adding argument(s) to cURL request', $arguments);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $arguments);
        }

        if (false === $response_data = curl_exec($ch)) {
            throw new \Exception(curl_error($ch));
        }

        $response_info    = curl_getinfo($ch);
        $response_headers = substr($response_data, 0, $response_info['header_size']);
        $response_body    = substr($response_data, $response_info['header_size']);

        if (isset($file)) {
            fclose($file);
        }

        curl_close($ch);

        $response = array_merge($response_info, array(
            'response'          => $response_data,
            'response_headers'  => explode("\r\n", $response_headers),
            'response_body'     => $response_body,
        ));

        $this->log('Received cURL response', $response);

        $this->setResponse($response);

        return $this;
    }
}