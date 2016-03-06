<?php

namespace Libcast\AssetDistributor\YouTube;

use Google_Http_MediaFileUpload as FileUpload;
use Google_Service_YouTube_VideoSnippet as Snippet;
use Google_Service_YouTube_VideoStatus as Status;
use Google_Service_YouTube_Video as Resource;
use Libcast\AssetDistributor\Adapter\AbstractAdapter;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\Video;
use Libcast\AssetDistributor\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class YouTubeAdapter extends AbstractAdapter implements Adapter
{
    /**
     *
     * @var string Json
     */
    protected $accessToken;

    /**
     *
     * @return string
     */
    public function getVendor()
    {
        return 'YouTube';
    }

    /**
     *
     * @return \Google_Service_YouTube
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $google = new \Google_Client;
        $google->setClientId($this->getConfiguration('id'));
        $google->setClientSecret($this->getConfiguration('secret'));
        $google->setApplicationName($this->getConfiguration('application'));
        $google->setAccessType($this->getConfiguration('access_type'));

        $google->setScopes($this->getConfiguration('scopes'));

        $google->setRedirectUri($this->getConfiguration('redirectUri'));

        if ($token = $this->getCredentials()) {
            $google->setAccessToken($token);

            if ($google->isAccessTokenExpired()) {
                $json = json_decode($token);
                $google->refreshToken($json->refresh_token);
                $this->setCredentials($google->getAccessToken());
            }

            $this->isAuthenticated = true;
        }

        $this->client = new \Google_Service_YouTube($google); // Now getClient() returns \Google_Service_YouTube

        if (!$this->isAuthenticated()) {
            $this->authenticate();
        }

        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $google = $this->getClient()->getClient();
        $request = Request::get();
        $session = new Session;

        if ($code = $request->query->get('code')) {
            if (!$requestState = $request->query->get('state')) {
                throw new \Exception('Missing state from YouTube request');
            }

            if (!$sessionState = $session->get('youtube_state')) {
                throw new \Exception('Missing state from YouTube session');
            }

            if (strval($requestState) !== strval($sessionState)) {
                throw new \Exception('YouTube session state and request state don\'t match');
            }

            $this->debug('Found YouTube oAuth code', ['code' => $code]);
            $google->authenticate($code);

            $session->set('youtube_token', $google->getAccessToken());
        }

        if ($token = $session->get('youtube_token')) {
            $this->debug('Found YouTube oAuth token', ['token' => $token]);
            $google->setAccessToken($token);
        }

        if ($accessToken = $google->getAccessToken()) {
            $this->accessToken = $accessToken;
            $this->setCredentials($accessToken);
        } else {
            $this->debug('Missing YouTube token, try to authenticate...');

            $state = mt_rand();
            $google->setState($state);
            $session->set('youtube_state', $state);

            $this->redirect($google->createAuthUrl());
        }

        // Clean query parameters as they may cause error
        // on other Adapter's authentication process
        $request->query->set('code', null);
        $request->query->set('state', null);

        $this->debug('YouTube account is authenticated');

        $this->isAuthenticated = true;
    }

    /**
     * {@inheritdoc}
     */
    public static function support(Asset $asset)
    {
        if (!$asset instanceof Video) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function upload(Asset $asset)
    {
        if (!self::support($asset)) {
            throw new \Exception('YouTube adapter only handles video assets');
        }

        if (!is_null($this->retrieve($asset))) {
            return;
        }

        $youtube = $this->getClient();

        $client = $youtube->getClient();
        $client->setDefer(true);

        $request = $youtube->videos->insert('status,snippet', $this->getResource($asset)); /** @var \Psr\Http\Message\RequestInterface $request */

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
            $this->upload($asset);
            return;
        }

        $youtube = $this->getClient();

        $resource = $this->getResource($asset);
        $resource->setId($video_id);

        $youtube->videos->update('status,snippet', $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Asset $asset)
    {
        if (!$video_id = $this->retrieve($asset)) {
            throw new \Exception('Asset is unknown to YouTube');
        }

        $youtube = $this->getClient();
        $youtube->videos->delete($video_id);

        $this->forget($asset);
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieve(Asset $asset)
    {
        if (!$video_id = parent::retrieve($asset)) {
            return null;
        }

        $youtube = $this->getClient();
        $videos = $youtube->videos->listVideos('snippet', ['id' => $video_id]);

        return count($videos) ? $video_id : null;
    }

    /**
     *
     * @param Asset $asset
     * @return \Google_Service_YouTube_VideoSnippet
     */
    protected function getSnippet(Asset $asset)
    {
        $snippet = new Snippet;
        $snippet->setTitle($asset->getTitle());

        if ($description = $asset->getDescription()) {
            $snippet->setDescription($description);
        }

        if ($tags = $asset->getTags()) {
            $snippet->setTags($tags);
        }

        if ($category_id = $asset->getCategory($this->getVendor())) {
            $snippet->setCategoryId($category_id);
        }

        return $snippet;
    }

    /**
     *
     * @param Asset $asset
     * @return Status
     * @throws \Exception
     */
    protected function getStatus(Asset $asset)
    {
        $status = new Status;

        switch (true) {
            case $asset->isPublic():
                $status->setPrivacyStatus('public');
                break;

            case $asset->isPrivate():
                $status->setPrivacyStatus('private');
                break;

            case $asset->isHidden():
                $status->setPrivacyStatus('hidden');
                break;

            default:
                throw new \Exception('Missing asset visibility for YouTube');
        }

        return $status;
    }

    /**
     *
     * @param Asset $asset
     * @return \Google_Service_YouTube_Video
     */
    protected function getResource(Asset $asset)
    {
        $resource = new Resource;
        $resource->setSnippet($this->getSnippet($asset));
        $resource->setStatus($this->getStatus($asset));

        return $resource;
    }
}
