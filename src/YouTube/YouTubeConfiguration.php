<?php

namespace Libcast\AssetDistributor\YouTube;

use Libcast\AssetDistributor\Configuration\AbstractConfiguration;
use Libcast\AssetDistributor\Configuration\Configuration;

class YouTubeConfiguration extends AbstractConfiguration implements Configuration
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration)
    {
        $configuration = array_merge($configuration, [
            'access_type' => 'offline',
            'scopes'      => [
                'https://www.googleapis.com/auth/youtube',
                'https://www.googleapis.com/auth/youtube.readonly',
                'https://www.googleapis.com/auth/youtube.upload',
            ],
        ]);

        parent::__construct($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryMap()
    {
        return [
            'film'          => 1,
            'animation'     => 1,
            'autos'         => 2,
            'vehicles'      => 2,
            'music'         => 10,
            'pets'          => 15,
            'animals'       => 15,
            'sports'        => 17,
            'clips'         => 18,
            'travel'        => 19,
            'events'        => 19,
            'gaming'        => 20,
            'videoblogging' => 21,
            'people'        => 22,
            'blogs'         => 22,
            'comedians'     => 23,
            'entertainment' => 24,
            'news'          => 25,
            'politics'      => 25,
            'howto'         => 26,
            'style'         => 26,
            'education'     => 27,
            'science'       => 28,
            'technology'    => 28,
            'nonprofits'    => 29,
            'activism'      => 29,
            'movies'        => 30,
            'anime'         => 31,
            'action'        => 32,
            'adventure'     => 32,
            'classics'      => 33,
            'comedy'        => 34,
            'documentary'   => 35,
            'drama'         => 36,
            'family'        => 37,
            'foreign'       => 38,
            'horror'        => 39,
            'sci-fi'        => 40,
            'fantasy'       => 40,
            'thriller'      => 41,
            'shorts'        => 42,
            'shows'         => 43,
            'trailers'      => 44,
        ];
    }
}
