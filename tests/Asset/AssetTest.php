<?php

use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\AssetFactory;
use Libcast\AssetDistributor\Asset\Image;
use Libcast\AssetDistributor\Asset\Video;

class AssetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Image
     */
    protected $image;

    /**
     * @var Video
     */
    protected $video;

    public function testAssetFactory()
    {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        $image = AssetFactory::build($dir . 'image.gif');
        $this->assertInstanceOf('\Libcast\AssetDistributor\Asset\Asset', $image);
        $this->assertInstanceOf('\Libcast\AssetDistributor\Asset\Image', $image);

        $video = AssetFactory::build($dir . 'video.mp4');
        $this->assertInstanceOf('\Libcast\AssetDistributor\Asset\Asset', $video);
        $this->assertInstanceOf('\Libcast\AssetDistributor\Asset\Video', $video);

        return $video;
    }

    /**
     * @depends testAssetFactory
     */
    public function testAsset(Asset $asset)
    {
        $asset->addTag('foo', 'bar');

        $this->assertEquals('video.mp4', $asset->getTitle());
        $this->assertEquals('video/mp4', $asset->getMimetype());
        $this->assertGreaterThan(0, $asset->getSize());
        $this->assertNull($asset->getDescription());
        $this->assertArrayHasKey('foo', $asset->getTags());
        $this->assertTrue($asset->isPublic());
        $this->assertFalse($asset->isPrivate());
        $this->assertFalse($asset->isHidden());
    }
}
