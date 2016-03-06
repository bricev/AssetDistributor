<?php

use Doctrine\Common\Cache\ArrayCache as Cache;
use Libcast\AssetDistributor\Asset\AssetFactory;
use Libcast\AssetDistributor\Adapter\AdapterCollection;
use Libcast\AssetDistributor\Owner;
use Libcast\AssetDistributor\Vimeo\VimeoAdapter;
use Libcast\AssetDistributor\YouTube\YouTubeAdapter;

class AdapterCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Owner
     */
    protected static $owner;

    /**
     * @var string
     */
    protected static $configurationPath;

    public static function setUpBeforeClass()
    {
        self::$owner = new Owner('bricev_asset_distributor_collection_test_owner', new Cache);
        self::$configurationPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'configuration.ini';
    }

    public function testAdapterCollection()
    {
        $collection = new AdapterCollection;
        $this->assertEmpty((array) $collection);

        $collection[] = new VimeoAdapter(self::$owner, self::$configurationPath);
        $collection[] = new YouTubeAdapter(self::$owner, self::$configurationPath);
        $this->assertCount(2, $collection);
    }

    /**
     * @expectedException \Exception
     */
    public function testAdapterCollectionException()
    {
        $collection = new AdapterCollection;
        $collection[] = 'foobar';
    }

    public function testRetrieveFromCache()
    {
        self::$owner->setAccount('YouTube', 'foobar');

        $collection = (array) AdapterCollection::retrieveFromCache(self::$owner, self::$configurationPath);
        $this->assertCount(1, $collection);

        $adapter = array_pop($collection);
        $this->assertInstanceOf('\Libcast\AssetDistributor\YouTube\YouTubeAdapter', $adapter);
    }

    public function testBuildForAsset()
    {
        $asset = AssetFactory::build(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'video.mp4');

        $collection = AdapterCollection::buildForAsset($asset, self::$owner, self::$configurationPath);
        $this->assertCount(2, $collection);
    }
}
