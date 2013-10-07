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

use Libcast\AssetDistribution\Request\AbstractRequest;

class HttpRequest extends AbstractRequest
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * 
     * @var string Request body
     */
    protected $body;

    /**
     * 
     * @var array Headers
     */
    protected $headers = array();

    public static function getMethods()
    {
        return array(
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
        );
    }

    /**
     * 
     * @param string $body
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * 
     * @return string Request body
     */
    protected function getBody()
    {
        return $this->body;
    }

    /**
     * 
     * @param array $headers
     * @return \Libcast\AssetDistribution\Request\RequestInterface
     */
    public function setHeaders($headers)
    {
        if (!is_array($headers)) {
            $headers = (array) $headers;
        }

        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * 
     * @return array List of headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 
     * @return bool True if param $name exists
     */
    public function hasHeaders()
    {
        return empty($this->headers);
    }

    /**
     * Redirects the browser
     * 
     * @return void
     */
    public function redirect()
    {
        $url = $this->getUrl();

        $arguments = $this->getArguments();
        if ($arguments) {
            $url = vsprintf($url, $arguments);
        }

        $this->log("Redirect to '$url'.");

        if (headers_sent()) {
            // if headers have already been sent, try to send HTML code to force 
            // the browser to redirect users

            die( '<noscript>'.PHP_EOL
                .'  <meta http-equiv="refresh" content="0; url='.$url.'" />'.PHP_EOL
                .'</noscript>'.PHP_EOL
                .'<script type="text/javascript">'.PHP_EOL
                .'  window.location.href="'.$url.'";'.PHP_EOL
                .'</script>'.PHP_EOL
                .'<a href="'.$url.'">'.$url.'</a>');
        } else {
            header("Location: $url");
            die;
        }
    }
}