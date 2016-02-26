<?php

namespace Libcast\AssetDistributor\Dailymotion;

use Libcast\AssetDistributor\Configuration\AbstractConfiguration;
use Libcast\AssetDistributor\Configuration\Configuration;

class DailymotionConfiguration extends AbstractConfiguration implements Configuration
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration)
    {
        $configuration = array_merge($configuration, [
            'scopes' => ['manage_videos'],
        ]);

        parent::__construct($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryMap()
    {
        return [];
    }
}
