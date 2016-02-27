<?php

use Libcast\AssetDistributor\Configuration\Configuration;
use Libcast\AssetDistributor\Configuration\ConfigurationFactory;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $configurationPath;

    public static function setUpBeforeClass()
    {
        self::$configurationPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'configuration.ini';
    }

    public function testConfigurationFactory()
    {
        $youtubeConfiguration = ConfigurationFactory::build('YouTube', self::$configurationPath);

        $this->assertInstanceOf('\Libcast\AssetDistributor\Configuration\Configuration', $youtubeConfiguration);
        $this->assertInstanceOf('\Libcast\AssetDistributor\YouTube\YouTubeConfiguration', $youtubeConfiguration);

        return $youtubeConfiguration;
    }

    public function testGetVendors()
    {
        $vendors = ConfigurationFactory::getVendors(self::$configurationPath);
        $this->assertTrue(in_array('YouTube', $vendors));
        $this->assertCount(2, $vendors);
    }

    /**
     * @depends testConfigurationFactory
     */
    public function testConfiguration(Configuration $configuration)
    {
        $this->assertEquals('foo', $configuration->get('id'));
        $this->assertNull($configuration->get('application'));
        $this->assertFalse(is_null($configuration->get('redirectUri')));
        $this->assertTrue(is_array($configuration->get('scopes')));
    }
}
