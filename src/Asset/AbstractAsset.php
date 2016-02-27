<?php

namespace Libcast\AssetDistributor\Asset;

use League\Flysystem\File;
use Libcast\AssetDistributor\Configuration\CategoryRegistry;

abstract class AbstractAsset
{
    const VISIBILITY_PUBLIC = 'public';

    const VISIBILITY_PRIVATE = 'private';

    const VISIBILITY_HIDDEN = 'hidden';

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
     * @var string
     */
    protected $category;

    /**
     *
     * @var string
     */
    protected $visibility = self::VISIBILITY_PUBLIC;

    /**
     *
     * @param File $file
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
        $filesystem = $this->file->getFilesystem(); /** @var \League\Flysystem\Filesystem $filesystem  */

        $filesystemAdapter = $filesystem->getAdapter(); /** @var \League\Flysystem\Adapter\AbstractAdapter $filesystemAdapter */

        return $filesystemAdapter->applyPathPrefix($this->file->getPath());
    }

    /**
     *
     * @return string
     */
    public function getMimetype()
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
        if (!$this->title) {
            $this->setTitle(pathinfo($this->getPath(), PATHINFO_BASENAME));
        }

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
     * @param $key
     * @param $value
     */
    public function addTag($key, $value)
    {
        $this->tags[$key] = $value;
    }

    /**
     *
     * @param null $vendor
     * @return mixed
     */
    public function getCategory($vendor = null)
    {
        if ($this->category && $vendor) {
            return CategoryRegistry::get($this->category, $vendor);
        }

        return $this->category;
    }

    /**
     *
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     *
     * @param $visibility
     * @throws \Exception
     */
    public function setVisibility($visibility)
    {
        if (!in_array($visibility, [
            self::VISIBILITY_PUBLIC,
            self::VISIBILITY_PRIVATE,
            self::VISIBILITY_HIDDEN,
        ])) {
            throw new \Exception("Visibility '$visibility' is not supported");
        }

        $this->visibility = $visibility;
    }

    /**
     *
     * @return bool
     */
    public function isPublic()
    {
        return self::VISIBILITY_PUBLIC === $this->getVisibility();
    }

    /**
     *
     * @return bool
     */
    public function isPrivate()
    {
        return self::VISIBILITY_PRIVATE === $this->getVisibility();
    }

    /**
     *
     * @return bool
     */
    public function isHidden()
    {
        return self::VISIBILITY_HIDDEN === $this->getVisibility();
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
