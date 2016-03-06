<?php

$root = dirname(__DIR__);

include "$root/vendor/autoload.php";

use Doctrine\Common\Cache\FilesystemCache as Cache;
use Libcast\AssetDistributor\Owner;
use Libcast\AssetDistributor\Asset\AssetFactory;
use Libcast\AssetDistributor\Adapter\AdapterCollection;
use Libcast\AssetDistributor\Dailymotion\DailymotionAdapter;
use Libcast\AssetDistributor\Vimeo\VimeoAdapter;
use Libcast\AssetDistributor\YouTube\YouTubeAdapter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$configPath = 'configuration.ini';

// Optional: set a logger
$logger = new Logger('distributor');
$logger->pushHandler(new StreamHandler("$root/example/debug.log", Logger::DEBUG));

// Create an Owner
$owner = new Owner('johndoe', new Cache("$root/example/cache"));

// Create an Asset
$asset = AssetFactory::build(
    "$root/tests/video.mp4",        // path to file, or a Flysystem\File object
    'My Video',                     // optional title
    'This is an awesome video',     // optional description
    ['test', 'asset-distributor']   // optional array of tags
);
$asset->setVisibility('private');   // Make video private (public by default)

// Create a collection of Adapters that will manage the Asset with Owner credentials
$adapters = new AdapterCollection;
$adapters[] = new YouTubeAdapter($owner, $configPath, $logger);
$adapters[] = new VimeoAdapter($owner, $configPath, $logger);
$adapters[] = new DailymotionAdapter($owner, $configPath, $logger);

// Affect the Adapter collection to the Owner
$owner->setAdapters($adapters);

// Now the Owner may manipulate the Asset
$owner->upload($asset);
