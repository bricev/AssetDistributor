<?php

namespace Libcast\AssetDistributor\YouTube;

use League\Flysystem\File;
use Google_Http_MediaFileUpload as FileUpload;
use Google_Service_YouTube_VideoSnippet as Snippet;
use Google_Service_YouTube_VideoStatus as Status;
use Google_Service_YouTube_Video as Resource;
use Libcast\AssetDistributor\Adapter\AbstractAdapter;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\Video;

/**
 *
 * @method \Google_Service_YouTube getClient()
 */
class YouTubeAdapter extends AbstractAdapter implements Adapter
{
    /**
     *
     * @var \Google_Service_YouTube_VideoSnippet
     */
    protected $snippet;

    /**
     *
     * @var \Google_Service_YouTube_VideoStatus
     */
    protected $status;

    /**
     *
     * @var \Google_Service_YouTube_Video
     */
    protected $resource;

    /**
     *
     * @param Video $asset
     * @throws \Exception
     * @see https://github.com/youtube/api-samples/blob/master/php/resumable_upload.php
     */
    public function upload(Asset $asset)
    {
        if (!$asset instanceof Video) {
            throw new \Exception('YouTube adapter only handles video assets');
        }

        $youtube = $this->getClient();

        $client = $youtube->getClient();
        $client->setDefer(true);

        $request = $youtube->videos->insert('status,snippet', $this->getResource($asset));

        $media = new FileUpload($client, $request, $asset->getMimetype(), null, true);
        $media->setChunkSize($chunkSize = 10 * 1024 * 1024);
        $media->setFileSize($asset->getSize());

        $status = false;
        $handle = fopen($asset->getPath(), 'rb');
        while (!$status and !feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $status = $media->nextChunk($chunk);
        }
        fclose($handle);

        $client->setDefer(false);

        $this->remember($asset, $status['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Asset $asset)
    {
        if (!$video_id = $this->retrieve($asset)) {
            throw new \Exception('File is unknown to YouTube');
        }

        $youtube = $this->getClient();

        $resource = $this->getResource($asset);
        $resource->setId($video_id);

        $youtube->videos->update('snippet', $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Asset $asset)
    {
        if (!$video_id = $this->retrieve($asset)) {
            throw new \Exception('Unknown file');
        }

        $youtube = $this->getClient();
        $youtube->videos->delete($video_id);

        $this->forget($asset);
    }

    /**
     *
     * @param $title
     * @param array $parameters
     * @return Snippet
     */
    protected function getSnippet($title, array $parameters = [])
    {
        if ($this->snippet) {
            return $this->snippet;
        }

        $this->snippet = new Snippet;
        $this->snippet->setTitle($title);

        if (isset($parameters['description']) and $description = $parameters['description']) {
            $this->snippet->setDescription($description);
        }

        if (isset($parameters['tags']) and $tags = $parameters['tags']) {
            $this->snippet->setTags($tags);
        }

        if (isset($parameters['category_id']) and $category_id = $parameters['category_id']) {
            $this->snippet->setCategoryId($category_id);
        }

        return $this->snippet;
    }

    /**
     *
     * @param string $status
     * @return \Google_Service_YouTube_VideoStatus
     */
    protected function getStatus($status = 'public')
    {
        if ($this->status) {
            return $this->status;
        }

        $this->status = new Status;
        $this->status->setPrivacyStatus($status);

        return $this->status;
    }

    /**
     *
     * @param File $file
     * @param array $parameters
     * @return \Google_Service_YouTube_Video
     */
    protected function getResource(File $file, array $parameters = [])
    {
        if ($this->resource) {
            return $this->resource;
        }

        if (!isset($parameters['title']) or !$title = $parameters['title']) {
            $title = pathinfo($file->getPath(), PATHINFO_BASENAME);
        }

        if (!isset($parameters['status']) or !$status = $parameters['status']) {
            $status = 'public';
        }

        $this->resource = new Resource;
        $this->resource->setSnippet($this->getSnippet($title, $parameters));
        $this->resource->setStatus($this->getStatus($status));

        return $this->resource;
    }
}
