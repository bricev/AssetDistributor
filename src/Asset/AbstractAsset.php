<?php

namespace Libcast\AssetDistributor\Asset;

use League\Flysystem\File;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Adapter\AdapterCollection;

abstract class AbstractAsset
{
    /**
     *
     * @var File
     */
    protected $file;

    /**
     *
     * @var string
     */
    protected $etag;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $description;

    /**
     *
     * @var array
     */
    protected $tags = [];

    /**
     *
     * @param File $file
     * @param array $adapters Array of Adapter objects
     */
    function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     *
     * @return string
     */
    public function getPath()
    {
        return $this->file->getPath();
    }

    /**
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->file->getMimetype();
    }

    /**
     *
     * @return int
     */
    public function getSize()
    {
        return $this->file->getSize();
    }

    /**
     *
     * @return string
     */
    public function getEtag()
    {
        if ($this->etag) {
            return $this->etag;
        }

        return $this->etag = md5(implode(':', [
            $this->getPath(),
            $this->getMimetype(),
            $this->getSize(),
        ]));
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     *
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     *
     * @return string
     */
    function __toString()
    {
        return $this->getEtag();
    }
}
