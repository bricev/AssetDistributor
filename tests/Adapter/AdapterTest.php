<?php

use Doctrine\Common\Cache\ArrayCache as Cache;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Adapter\AdapterFactory;
use Libcast\AssetDistributor\Asset\AssetFactory;
use Libcast\AssetDistributor\Owner;
use Libcast\AssetDistributor\YouTube\YouTubeAdapter;

class AdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $dir;

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
        self::$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        self::$owner = new Owner('bricev_asset_distributor_adapter_test_owner', new Cache);
        self::$configurationPath = self::$dir . 'configuration.ini';
    }

    /**
     * @expectedException \Exception
     */
    public function testGetClassName()
    {
        AdapterFactory::getClassName('foobar');
    }

    public function testAdapterFactory()
    {
        $adapter = AdapterFactory::build('YouTube', self::$owner, self::$configurationPath);
        $this->assertEquals('YouTube', $adapter->getVendor());

        return $adapter;
    }

    /**
     * @depends testAdapterFactory
     */
    public function testAdapter(Adapter $adapter)
    {
        $this->assertFalse($adapter->isAuthenticated());

        $image = AssetFactory::build(self::$dir . 'image.gif');
        $this->assertFalse($adapter->support($image));

        $video = AssetFactory::build(self::$dir . 'video.mp4');
        $this->assertTrue($adapter->support($video));
    }
}
