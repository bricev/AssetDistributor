<?php

use Doctrine\Common\Cache\ArrayCache as Cache;
use Libcast\AssetDistributor\Owner;

class OwnerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static  $identifier;

    /**
     * @var Cache
     */
    protected static $cache;

    public static function setUpBeforeClass()
    {
        self::$identifier = uniqid();
        self::$cache = new Cache;
    }

    public function testOwner()
    {
        $owner = new Owner(self::$identifier, self::$cache);

        $this->assertInstanceOf('\Libcast\AssetDistributor\Adapter\AdapterCollection', $owner->getAdapters());
        $this->assertTrue(is_array($owner->getAccounts()));
        $this->assertEmpty($owner->getAccounts());

        return $owner;
    }

    /**
     * @depends testOwner
     */
    public function testOwnerAccounts(Owner $owner)
    {
        $owner->setAccount('YouTube', 'foobar');
        $owner->setAccount('Vimeo', 'barbaz');
        $this->assertNotEmpty($accounts = $owner->getAccounts());
        $this->assertArrayHasKey('YouTube', $accounts);
        $this->assertEquals('foobar', $accounts['YouTube']);

        return $accounts;
    }

    /**
     * @depends testOwnerAccounts
     */
    public function testRetrieveFromCache(array $accounts)
    {
        $configurationPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'configuration.ini';

        $owner = Owner::retrieveFromCache(self::$identifier, $configurationPath, self::$cache);

        $this->assertEquals(self::$identifier, $owner->getIdentifier());

        $this->assertNotEmpty($owner->getAccounts());

        foreach ($accounts as $vendor => $credentials) {
            $this->assertArrayHasKey($vendor, $savedAccounts = $owner->getAccounts());
            $this->assertEquals($credentials, $savedAccounts[$vendor]);
        }
    }
}
