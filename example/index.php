<?php

include __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Cache\FilesystemCache as Cache;
use League\Flysystem\File;
use Libcast\AssetDistributor\Configuration;
use Libcast\AssetDistributor\Owner;
use Libcast\AssetDistributor\Adapter\AdapterFactory;
use Libcast\AssetDistributor\Asset\AssetFactory;

$cache = new Cache('/tmp');

$configuration = new Configuration('configuration.ini');

$owner = new Owner('messa', $cache);

$youtube = AdapterFactory::build('YouTube', $configuration, $owner);
$owner->addAdapter($youtube);

$asset = AssetFactory::build(new File('/path/to/file.mp4'), 'My Video');
$owner->upload($asset);
