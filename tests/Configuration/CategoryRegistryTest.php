<?php

use Libcast\AssetDistributor\Vimeo\VimeoConfiguration;
use Libcast\AssetDistributor\YouTube\YouTubeConfiguration;
use Libcast\AssetDistributor\Configuration\CategoryRegistry;

class CategoryRegistryTest extends PHPUnit_Framework_TestCase
{
    public function testGetVimeoCategoryMap()
    {
        $configuration = new VimeoConfiguration([]);

        $categories = $configuration->getCategoryMap();

        $this->assertArrayHasKey('fashion', $categories);

        return [
            'vendor' => 'Vimeo',
            'categories' => $categories,
        ];
    }

    public function testGetYouTubeCategoryMap()
    {
        $configuration = new YouTubeConfiguration([]);

        $categories = $configuration->getCategoryMap();

        $this->assertArrayHasKey('adventure', $categories);

        return [
            'vendor' => 'YouTube',
            'categories' => $categories,
        ];
    }

    /**
     * @depends testGetVimeoCategoryMap
     * @depends testGetYouTubeCategoryMap
     */
    public function testCategoryRegistry(array $categories)
    {
        // Registers categories from all tested vendors
        foreach (func_get_args() as $arg) {
            CategoryRegistry::addVendorCategories($arg['vendor'], $arg['categories']);
        }

        $this->assertEquals('/categories/sports', CategoryRegistry::get('sports', 'Vimeo'));
        $this->assertEquals(17, CategoryRegistry::get('sports', 'YouTube'));

        $this->assertTrue(CategoryRegistry::has('documentary')); // both present on Vimeo and YouTube
        $this->assertTrue(CategoryRegistry::has('gaming')); // YouTube only
        $this->assertTrue(CategoryRegistry::has('narrative')); // Vimeo only

        $this->assertFalse(CategoryRegistry::has('pornography'));
    }
}
