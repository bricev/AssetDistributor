<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Provider\Manager;

use Libcast\AssetDistribution\Provider\Manager\ManagerInterface;
use Libcast\AssetDistribution\Provider\Manager\AbstractManager;
use Libcast\AssetDistribution\Asset\AssetInterface;
use Libcast\AssetDistribution\Asset\AbstractAsset;
use Libcast\AssetDistribution\Asset\Asset;
use Libcast\AssetDistribution\Request\CurlRequest;

class YoutubeManager extends AbstractManager implements ManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        if (!$this->is_new) {
            // asset has been flagged as no new on the provider
            // it means it has already be found
            return false;
        }

        if (!$this->hasProviderParameter('video_id')) {
            // asset has no trace of a provider reference
            // so asset is new
            return true;
        }

        // asset has a trace of provider reference
        // let's try to find it on the remote provider
        return $this->find(null, false) instanceof AssetInterface ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function find($key = null, $overwrite = true)
    {
        if (!$key) {
            if (!$this->hasProviderParameter('video_id')) {
                throw new \Exception('Missing \'video_id\' parameter.');
            }

            $key = $this->getProviderParameter('video_id');
        } else {
            $this->setProviderParameter('video_id', $key);
        }

        $provider = $this->getProvider();
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $credentials = $provider->getCredentials();
        /* @var $credentials \Libcast\AssetDistribution\Provider\Credentials\YoutubeCredentials */

        $asset = $this->getAsset();
        /* @var $asset \Libcast\AssetDistribution\Asset\VideoAsset */

        $request = new CurlRequest($provider->getLogger());
        $request->setUrl($provider->getSetting('data_url')."?id=$key&maxResults=1&part=snippet,status")
                ->setHeaders("Authorization: Bearer {$credentials->getAccessToken()}")
                ->get();

        $resource = json_decode($request->getResponse(), true);
        $video    = $resource['items'][0];

        if (!isset($video['snippet']) || !isset($video['snippet'])) {
            throw new \Exception('Impossible to find the video.');
        }

        // mark the video as existing on YouTube
        $this->is_new = false;

        if (!$overwrite) {
            return parent::find($key);
        }

        // load data from YouTube snippet and status
        foreach (array_merge($video['snippet'], $video['status']) as $key => $value) {
            // find common name for $key and – if exists – set asset parameter
            $common_name = $provider->getCommonFieldName($key);
            if (in_array($common_name, AbstractAsset::getCommonFields())) {
                $asset->setParameter($common_name, $value);
            }

            // set provider parameter
            $this->setProviderParameter($key, $value);
        }

        // set visibility
        if (isset($video['privacyStatus']) && $visibility = $video['privacyStatus']) {
            switch ($visibility) {
                case 'public':
                    $asset->setVisibility(AbstractAsset::VISIBILITY_VISIBLE);
                    break;

                case 'private':
                    $asset->setVisibility(AbstractAsset::VISIBILITY_PRIVATE);
                    break;

                case 'unlisted':
                default :
                    $asset->setVisibility(AbstractAsset::VISIBILITY_HIDDEN);
                    break;
            }
        }

        return parent::find($key);
    }

    /**
     * {@inheritdoc}
     */
    public function upload()
    {
        $provider = $this->getProvider();
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $credentials = $provider->getCredentials();
        /* @var $credentials \Libcast\AssetDistribution\Provider\Credentials\YoutubeCredentials */

        $asset = $this->getAsset();
        /* @var $asset \Libcast\AssetDistribution\Asset\VideoAsset */

        $size = filesize($asset->getPath());
        $mime_type = Asset::getFileType($asset->getPath());

        // reserve a resumable download URL
        $request = new CurlRequest($provider->getLogger());
        $request->setUrl($provider->getSetting('upload_url').'?uploadType=resumable&part=snippet,status,contentDetails')
                ->setBody($this->getVideoResource())
                ->setHeaders(array(
                    "Authorization: Bearer {$credentials->getAccessToken()}",
                    'Content-Type: application/json; charset=UTF-8',
                    "X-Upload-Content-Length: $size",
                    "X-Upload-Content-Type: $mime_type",
                ))
                ->post();

        $location = null;
        foreach ($request->getResponse('response_headers') as $header) {
            if (false !== strstr($header, 'Location: ', false)) {
                $location = substr($header, 10);
                break;
            }
        }

        if (!$location) {
            $provider->log('Youtube did not provide an upload URL', $request->getResponse());

            throw new \Exception('Impossible to initiate a YouTube upload.');
        }

        // start upload
        while (!$this->is_uploaded) {
            $upload = new CurlRequest($provider->getLogger());
            $upload->setUrl($location)
                    ->setBody("@{$asset->getPath()}")
                    ->setHeaders(array(
                        "Authorization: Bearer {$credentials->getAccessToken()}",
                        "Content-Length: $size",
                        "Content-Type: $mime_type",
                    ))
                    ->put();


            if (200 == $upload->getResponse('http_code')) {
                // sucessfull upload
                $this->is_uploaded = true;

                // load video data from YouTube
                $video = json_decode($upload->getResponse('response_body'), true);

                if (!isset($video['id'])) {
                    throw new \Exception('Impossible to retrieve the video.');
                }

                return $this->find($video['id']);
            } else {
                // upload failed, let's try to resume the transfert

                /* @todo finish upload resume */
                /* @see https://developers.google.com/youtube/v3/guides/using_resumable_upload_protocol */
                throw new \Exception('Impossible to complete upload.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        $provider = $this->getProvider();
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $credentials = $provider->getCredentials();
        /* @var $credentials \Libcast\AssetDistribution\Provider\Credentials\YoutubeCredentials */

        $request = new CurlRequest($provider->getLogger());
        $request->setUrl($provider->getSetting('data_url').'?part=snippet,status')
                ->setBody($this->getVideoResource(array(
                    'id'      => $this->getProviderParameter('video_id'),
                    'videoId' => $this->getProviderParameter('video_id'),
                )))
                ->setHeaders(array(
                    'Content-Type: application/json; charset=UTF-8',
                    "Authorization: Bearer {$credentials->getAccessToken()}",
                ))
                ->put();

        if (200 == $request->getResponse('http_code')) {
            // re-load video data from YouTube
            $video = json_decode($request->getResponse('response_body'), true);

            if (!isset($video['id'])) {
                throw new \Exception('Impossible to retrieve the video.');
            }

            return $this->find($video['id']);
        } else {
            $response = $request->getResponse('response_body');
            if (isset($response['error']['message'])) {
                throw new \Exception($response['error']['message']);
            }

            throw new \Exception('An error occured.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $provider = $this->getProvider();
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $credentials = $provider->getCredentials();
        /* @var $credentials \Libcast\AssetDistribution\Provider\Credentials\YoutubeCredentials */

        $request = new CurlRequest($provider->getLogger());
        $request->setUrl($provider->getSetting('data_url')."?id={$this->getProviderParameter('video_id')}")
                ->setHeaders("Authorization: Bearer {$credentials->getAccessToken()}")
                ->delete();

        if (200 == $request->getResponse('http_code')) {
            return parent::delete();
        } else {
            throw new \Exception('An error occured.');
        }
    }

    /**
     *
     * @param  array $root Base of the video resource
     * @return string YouTube Video Resource in Json format
     */
    protected function getVideoResource(array $root = array())
    {
        $provider = $this->getProvider();
        /* @var $provider \Libcast\AssetDistribution\Provider\YoutubeProvider */

        $asset = $this->getAsset();
        /* @var $asset \Libcast\AssetDistribution\Asset\VideoAsset */

        // collect snippet data
        $snippet = array();
        foreach (array('title', 'description', 'keywords', 'category') as $field) {
            if ($asset->hasParameter($field)) {
                $snippet[$provider->getProviderFieldName($field)] = $asset->getParameter($field);
            } elseif ($this->hasProviderParameter($provider->getProviderFieldName($field))) {
                $snippet[$provider->getProviderFieldName($field)] = $this->getProviderParameter($provider->getProviderFieldName($field));
            }
        }

        // set a default title if null
        if (!isset($snippet['title'])) {
            $snippet['title'] = pathinfo($asset->getPath(), PATHINFO_FILENAME);
        }

        // collect status data
        $status = array();
        foreach (array('embeddable', 'publicStatsViewable') as $field) {
            if ($asset->hasParameter($field)) {
                $status[$provider->getProviderFieldName($field)] = $asset->getParameter($field);
            } elseif ($this->hasProviderParameter($provider->getProviderFieldName($field))) {
                $status[$provider->getProviderFieldName($field)] = $this->getProviderParameter($provider->getProviderFieldName($field));
            }
        }

        // get license
        $status['license'] = 'youtube';

        // get privacy status
        switch ($asset->getVisibility()) {
            case AbstractAsset::VISIBILITY_VISIBLE:
                $status['privacyStatus'] = 'public';
                break;

            case AbstractAsset::VISIBILITY_PRIVATE:
                $status['privacyStatus'] = 'private';
                break;

            case AbstractAsset::VISIBILITY_HIDDEN:
            default :
                $status['privacyStatus'] = 'unlisted';
        }

        return (string) json_encode(array_merge($root, array(
            'snippet' => $snippet,
            'status'  => $status,
        )));
    }
}