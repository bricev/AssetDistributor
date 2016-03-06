<?php

namespace Libcast\AssetDistributor\Dailymotion;

use Libcast\AssetDistributor\Configuration\AbstractConfiguration;
use Libcast\AssetDistributor\Configuration\Configuration;
use Psr\Log\LoggerInterface;

class DailymotionConfiguration extends AbstractConfiguration implements Configuration
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, LoggerInterface $logger = null)
    {
        $configuration = array_merge($configuration, [
            'scopes' => ['manage_videos'],
        ]);

        parent::__construct($configuration, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryMap()
    {
        return [];
    }
}
