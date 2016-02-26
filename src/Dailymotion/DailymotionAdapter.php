<?php

namespace Libcast\AssetDistributor\Dailymotion;

use Libcast\AssetDistributor\Adapter\AbstractAdapter;
use Libcast\AssetDistributor\Adapter\Adapter;
use Libcast\AssetDistributor\Asset\Asset;
use Libcast\AssetDistributor\Asset\Video;
use Dailymotion;

class DailymotionAdapter extends AbstractAdapter implements Adapter
{
    /**
     *
     * @var array
     */
    protected $resource;

    /**
     *
     * @return string
     * @throws \Exception
     */
    public function getVendor()
    {
        return 'Dailymotion';
    }

    /**
     *
     * @return Dailymotion
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new Dailymotion();
        $client->setGrantType(
            Dailymotion::GRANT_TYPE_AUTHORIZATION,
            $this->getConfiguration('key'),
            $this->getConfiguration('secret'),
            $this->getConfiguration('scopes')
        );

        if ($session = $this->getCredentials()) {
            $client->setSession($session);
        }

        return $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $client = $this->getClient();

        try {
            $client->getAccessToken();
        } catch (\DailymotionAuthRequiredException $e) {
            $this->redirect($client->getAuthorizationUrl());
        }

        $this->setCredentials($client->getSession());

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
            throw new \Exception('Dailymotion adapter only handles video assets');
        }

        $dailymotion = $this->getClient();

        // Upload video
        $url = $dailymotion->uploadFile($asset->getPath());

        // Publish video
        $request = $dailymotion->post('/me/videos', $this->getResource($asset, $url));

        $this->remember($asset, $request['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Asset $asset)
    {
        if (!$id = $this->retrieve($asset)) {
            throw new \Exception('Asset is unknown to Dailymotion');
        }

        $dailymotion = $this->getClient();

        // Update video
        $dailymotion->post("/video/$id", $this->getResource($asset));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Asset $asset)
    {
        if (!$id = $this->retrieve($asset)) {
            throw new \Exception('Asset is unknown to Dailymotion');
        }

        $dailymotion = $this->getClient();

        // Delete video
        $dailymotion->delete("/video/$id");

        $this->forget($asset);
    }

    /**
     *
     * @param Asset  $asset
     * @param string $url
     * @return array
     */
    protected function getResource(Asset $asset, $url = null)
    {
        $resource = [
            'title'       => $asset->getTitle(),
            'description' => $asset->getDescription(),
            'tags'        => $asset->getTags(),
            'published'   => !$asset->isHidden(),
            'private'     => $asset->isPrivate(),
        ];

        if ($url) {
            $resource['url'] = $url;
        }

        return array_filter($resource);
    }
}
