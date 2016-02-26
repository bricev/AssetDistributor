<?php

include __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Cache\FilesystemCache as Cache;
use League\Flysystem\File;
use Libcast\AssetDistributor\Owner;
use Libcast\AssetDistributor\Asset\AssetFactory;
use Libcast\AssetDistributor\Adapter\AdapterCollection;
use Libcast\AssetDistributor\YouTube\YouTubeAdapter;
use Libcast\AssetDistributor\Vimeo\VimeoAdapter;

$configPath = 'configuration.ini';

$asset = AssetFactory::build(new File('/path/to/file.mp4'), 'My Video');

$owner = new Owner('messa', new Cache('/tmp'));

$adapters = AdapterCollection::retrieveFromCache($owner, $configPath);
$adapters[] = new YouTubeAdapter($owner, $configPath);
$adapters[] = new VimeoAdapter($owner, $configPath);

$owner->setAdapters($adapters);

$owner->upload($asset);
