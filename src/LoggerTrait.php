<?php

namespace Libcast\AssetDistributor;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

trait LoggerTrait
{
    /**
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof LoggerInterface) {
            $this->logger = new NullLogger;
        }

        return $this->logger;
    }

    /**
     *
     * @param string $message
     * @param array $context
     */
    public function log($message, $context = [], $level = LogLevel::DEBUG)
    {
        $this->getLogger()->log($level, $message, (array) $context);
    }

    /**
     *
     * @param string $message
     * @param array $context
     */
    public function debug($message, $context = [])
    {
        $this->log($message, $context);
    }

    /**
     *
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = [])
    {
        $this->log($message, $context, LogLevel::INFO);
    }

    /**
     *
     * @param string $message
     * @param array $context
     */
    public function warning($message, $context = [])
    {
        $this->log($message, $context, LogLevel::WARNING);
    }

    /**
     *
     * @param string $message
     * @param array  $context
     */
    public function error($message, $context = [])
    {
        $this->log($message, $context, LogLevel::ERROR);
    }
}
