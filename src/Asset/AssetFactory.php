<?php

namespace Libcast\AssetDistributor\Asset;

use League\Flysystem\File;

class AssetFactory
{
    const AUDIO = 'Audio';
    const DOCUMENT = 'Document';
    const IMAGE = 'Image';
    const VIDEO = 'Video';

    /**
     *
     * @param File $file
     * @param $title
     * @param string $description
     * @param array $tags
     * @return Audio|Document|Image|Video
     * @throws \Exception
     */
    public static function build(File $file, $title, $description = '', array $tags = [])
    {
        if (!$type = self::guessType($file->getMimetype())) {
            throw new \Exception('This file is not supported');
        }

        $class = sprintf('\Libcast\AssetDistributor\Asset\%s', $type);

        $asset = new $class($file); /** @var $asset AbstractAsset */
        $asset->setTitle($title);
        $asset->setDescription($description);
        $asset->setTags($tags);

        return $asset;
    }

    /**
     *
     * @param $mimetype
     * @return null
     */
    private static function guessType($mimetype)
    {
        $mimetype = strtolower($mimetype);

        switch ($mimetype) {
            case 'application/ogg':
                return self::AUDIO;

            case 'application/excel':
            case 'application/x-msexcel':
            case 'application/vnd.ms-excel':
            case 'application/vnd.ms-excel.sheet.macroenabled.12':
            case 'application/vnd.ms-office':
            case 'application/vnd.oasis.opendocument.text':
            case 'application/vnd.sealed.xls':
            case 'application/vnd.sealedmedia.softseal.pdf':
            case 'application/msword':
            case 'application/pdf':
            case 'application/x-pdf':
            case 'application/mspowerpoint':
            case 'application/vnd.ms-powerpoint':
            case 'application/postscript':
            case 'application/rtf':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return self::DOCUMENT;

            case 'application/eps':
            case 'application/pcx':
            case 'application/x-pcx':
            case 'video/x-mng ':
                return self::IMAGE;

            case 'application/mp4':
            case 'application/mxf':
            case 'application/ogv':
            case 'application/vnd.ms-asf':
            case 'application/vnd.rn-realmedia-vbr':
            case 'application/vnd.rn-realmedia':
                return self::VIDEO;
        }

        $types = [
            self::AUDIO,
            self::DOCUMENT,
            self::IMAGE,
            self::VIDEO,
        ];

        foreach ($types as $type) {
            if (strtolower($type) === strstr($mimetype, '/', true)) {
                return $type;
            }
        }

        return null;
    }
}
