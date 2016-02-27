<?php

$root = dirname(__DIR__);

include "$root/vendor/autoload.php";

use Doctrine\Common\Cache\FilesystemCache as Cache;
use Libcast\AssetDistributor\Owner;
use Libcast\AssetDistributor\Asset\AssetFactory;
use Libcast\AssetDistributor\Adapter\AdapterCollection;
use Libcast\AssetDistributor\YouTube\YouTubeAdapter;
use Libcast\AssetDistributor\Vimeo\VimeoAdapter;

$configPath = 'configuration.ini';

// Create an Owner
$owner = new Owner('messa', new Cache('/tmp'));

// Create an Asset
$asset = AssetFactory::build(
    "$root/tests/video.mp4",        // path to file of a Flysystem\File object
    'My Video',                     // optional title
    'This is an awesome video',     // optional description
    ['test', 'asset-distributor']   // optional array of tags
);
$asset->setVisibility('private');

// Create a collection of Adapters that will manage the Asset with Owner credentials
$adapters = AdapterCollection::buildForAsset($asset, $owner, $configPath);
$adapters[] = new YouTubeAdapter($owner, $configPath);
$adapters[] = new VimeoAdapter($owner, $configPath);

// Affect the Adapter collection to the Owner
$owner->setAdapters($adapters);

// Now the Owner may manipulate the Asset
$owner->upload($asset);
