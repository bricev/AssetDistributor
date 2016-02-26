<?php

namespace Libcast\AssetDistributor\Configuration;

class CategoryRegistry
{
    /**
     *
     * @var array
     */
    protected static $categories = [];

    /**
     *
     * @param $category
     * @return bool
     */
    public static function has($category)
    {
        return in_array($category, array_keys(self::$categories));
    }

    /**
     *
     * @param $category
     * @param $vendor
     * @return mixed
     */
    public static function get($category, $vendor)
    {
        if (!self::has($category)) {
            return null;
        }

        $categoryVendors= self::$categories[$category];

        if (isset($categoryVendors[$vendor]) and $id = $categoryVendors[$vendor]) {
            return $id;
        }

        return null;
    }

    /**
     *
     * @param string $vendor
     * @param array  $map
     */
    public static function addVendorCategories($vendor, array $map)
    {
        foreach ($map as $category => $id) {
            if (!isset(self::$categories[$category])) {
                self::$categories[$category] = [];
            }

            self::$categories[$category][$vendor] = $id;
        }
    }
}
