<?php

namespace Libcast\AssetDistributor\Asset;

interface Asset
{
    /**
     *
     * @return string
     */
    public function getPath();

    /**
     *
     * @return string
     */
    public function getMimetype();

    /**
     *
     * @return string
     */
    public function getSize();

    /**
     *
     * @return string
     */
    public function getTitle();

    /**
     *
     * @return string
     */
    public function getDescription();

    /**
     *
     * @return array
     */
    public function getTags();

    /**
     *
     * @param null $vendor
     * @return mixed
     */
    public function getCategory($vendor = null);

    /**
     *
     * @return string
     */
    public function getVisibility();

    /**
     *
     * @return bool
     */
    public function isPublic();

    /**
     *
     * @return bool
     */
    public function isPrivate();

    /**
     *
     * @return bool
     */
    public function isHidden();
}
