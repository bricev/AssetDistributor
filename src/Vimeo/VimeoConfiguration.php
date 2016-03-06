<?php

namespace Libcast\AssetDistributor\Vimeo;

use Libcast\AssetDistributor\Configuration\AbstractConfiguration;
use Libcast\AssetDistributor\Configuration\Configuration;
use Psr\Log\LoggerInterface;

class VimeoConfiguration extends AbstractConfiguration implements Configuration
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, LoggerInterface $logger = null)
    {
        $configuration = array_merge($configuration, [
            'scopes' => ['public', 'private', 'upload', 'create', 'edit', 'delete'],
        ]);

        parent::__construct($configuration, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryMap()
    {
        return [
            'animation'      => '/categories/animation',
            'art'            => '/categories/art',
            'design'         => '/categories/art',
            'camera'         => '/categories/cameratechniques',
            'photo'          => '/categories/cameratechniques',
            'comedy'         => '/categories/comedy',
            'documentary'    => '/categories/documentary',
            'experimental'   => '/categories/experimental',
            'fashion'        => '/categories/fashion',
            'food'           => '/categories/food',
            'instructionals' => '/categories/instructionals',
            'music'          => '/categories/music',
            'narrative'      => '/categories/narrative',
            'personal'       => '/categories/personal',
            'journalism'     => '/categories/journalism',
            'sports'         => '/categories/sports',
            'talks'          => '/categories/talks',
            'travel'         => '/categories/travel',
        ];
    }
}
