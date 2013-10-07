<?php

/*
 * This file is part of AssetDistribution.
 *
 * (c) Brice Vercoustre <brcvrcstr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Libcast\AssetDistribution\Asset;

use Libcast\AssetDistribution\Asset\VideoAsset;
use Psr\Log\LoggerInterface;

class Asset
{
    const TYPE_AUDIO    = 'audio';

    const TYPE_DOCUMENT = 'document';

    const TYPE_IMAGE    = 'image';

    const TYPE_VIDEO    = 'video';

    /**
     * Create a new asset to manage accross providers.
     * 
     * @param  string                       $path      File path
     * @param  Provider|ProviderCollection  $provider  Provider(s) to manage to file with.
     * @param  LoggerInterface              $logger    Psr logger
     * @return \Libcast\AssetDistribution\Asset\AssetInterface
     * @throws \Exception
     */
    public static function load($path, &$provider = null, LoggerInterface $logger = null)
    {
        if (!file_exists($path) || !filesize($path)) {
            throw new \Exception("File '$path' does not exists.");
        }

        switch ($type = self::getFileFormat($path)) {
            case self::TYPE_VIDEO: 
                return new VideoAsset($path, $provider, $logger);

            case self::TYPE_AUDIO:
            case self::TYPE_DOCUMENT:
            case self::TYPE_IMAGE:
            default: 
                throw new \Exception("Type '$type' is not yet supported.");
        }
    }

    /**
     * Retrieve a file mime-type. 
     * Uses unix `file` application.
     * 
     * @param   string     $path  File path
     * @return  string            Mime-type
     * @throws  \Exception
     */
    public static function getFileType($path)
    {
        if (!$mime = trim(shell_exec(sprintf('file -b --mime-type %s', $path)))) {
            throw new \Exception("Impossible to read mime-type for '$path'.");
        }

        return $mime;
    }

    /**
     * Retrieve a file format based on its mime-type.
     * 
     * @param   string     $path  File path
     * @return  string            audio|document|image|video|null
     * @throws  \Exception
     */
    protected static function getFileFormat($path)
    {
        switch (self::getFileType($path)) {
            case 'application/ogg':
            case 'audio/3gpp':
            case 'audio/aiff':
            case 'audio/amr':
            case 'audio/amr-wb':
            case 'audio/annodex':
            case 'audio/basic':
            case 'audio/evrc':
            case 'audio/flac':
            case 'audio/l16':
            case 'audio/midi':
            case 'audio/mpeg':
            case 'audio/mp4':
            case 'audio/ogg':
            case 'audio/prs.sid':
            case 'audio/qcelp':
            case 'audio/smv':
            case 'audio/speex':
            case 'audio/vnd.audiokoz':
            case 'audio/vnd.digital-winds':
            case 'audio/vnd.everad.plj':
            case 'audio/vnd.lucent.voice':
            case 'audio/vnd.nokia.mobile-xmf':
            case 'audio/vnd.nortel.vbk':
            case 'audio/vnd.nuera.ecelp4800':
            case 'audio/vnd.nuera.ecelp7470':
            case 'audio/vnd.nuera.ecelp9600':
            case 'audio/vnd.rn-realaudio':
            case 'audio/vnd.sealedmedia.softseal.mpeg':
            case 'audio/vnd.wave':
            case 'audio/vorbis':
            case 'audio/voxware':
            case 'audio/wav':
            case 'audio/wave':
            case 'audio/webm':
            case 'audio/x-aiff':
            case 'audio/x-matroska':
            case 'audio/x-mid':
            case 'audio/x-midi':
            case 'audio/x-mpeg':
            case 'audio/x-mpegurl':
            case 'audio/x-ms-wma':
            case 'audio/x-ms-wax':
            case 'audio/x-ms-wmv':
            case 'audio/x-pn-realaudio':
            case 'audio/x-pn-realaudio-plugin':
            case 'audio/x-realaudio':
            case 'audio/x-wav':
                return self::TYPE_AUDIO;

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
                return self::TYPE_DOCUMENT;

            case 'application/eps':
            case 'application/pcx':
            case 'application/x-pcx':
            case 'image/bmp':
            case 'image/cewavelet':
            case 'image/cis-cod':
            case 'image/fif':
            case 'image/gif':
            case 'image/ief':
            case 'image/jp2':
            case 'image/jpeg':
            case 'image/jpeg':
            case 'image/jpm':
            case 'image/jpx':
            case 'image/pcx':
            case 'image/pict':
            case 'image/pjpeg':
            case 'image/png':
            case 'image/svg':
            case 'image/svg+xml':
            case 'image/targa':
            case 'image/tiff':
            case 'image/vn-svf':
            case 'image/vnd.dgn':
            case 'image/vnd.djvu':
            case 'image/vnd.dwg':
            case 'image/vnd.glocalgraphics.pgb':
            case 'image/vnd.microsoft.icon':
            case 'image/vnd.ms-modi':
            case 'image/vnd.sealed.png':
            case 'image/vnd.sealedmedia.softseal.gif':
            case 'image/vnd.sealedmedia.softseal.jpg':
            case 'image/vnd.wap.wbmp':
            case 'image/webp':
            case 'image/x-bmp':
            case 'image/x-cmu-raster':
            case 'image/x-freehand':
            case 'video/x-mng ':
            case 'image/x-ms-bmp':
            case 'image/x-pc-paintbrush':
            case 'image/x-pcx':
            case 'image/x-png':
            case 'image/x-portable-anymap':
            case 'image/x-portable-bitmap':
            case 'image/x-portable-graymap':
            case 'image/x-portable-pixmap':
            case 'image/x-rgb':
            case 'image/x-xbitmap':
            case 'image/x-xpixmap':
            case 'image/x-xwindowdump':
                return self::TYPE_IMAGE;

            case 'application/mp4':
            case 'application/mxf':
            case 'application/ogv':
            case 'application/vnd.ms-asf':
            case 'application/vnd.rn-realmedia-vbr':
            case 'application/vnd.rn-realmedia':
            case 'video/3gpp':
            case 'video/3gpp2':
            case 'video/annodex':
            case 'video/avi':
            case 'video/divx':
            case 'video/dl':
            case 'video/dvd':
            case 'video/gl':
            case 'video/mj2':
            case 'video/mpeg':
            case 'video/mpeg':
            case 'video/mp1s':
            case 'video/mp2t':
            case 'video/mp2p':
            case 'video/mp4':
            case 'video/mpv':
            case 'video/msvideo':
            case 'video/ogg':
            case 'video/quicktime':
            case 'video/vdo':
            case 'video/vivo':
            case 'video/vnd.avi':
            case 'video/vnd.fvt':
            case 'video/vnd.mpegurl':
            case 'video/vnd.nokia.interleaved-multimedia':
            case 'video/vnd.objectvideo':
            case 'video/vnd.sealed.mpeg1':
            case 'video/vnd.sealed.mpeg4':
            case 'video/vnd.sealedmedia.softseal.mov':
            case 'video/vnd.vivo':
            case 'video/webm':
            case 'video/x-dvm':
            case 'video/x-flc':
            case 'video/x-fli':
            case 'video/x-flv':
            case 'video/x-matroska':
            case 'video/x-matroska-3d':
            case 'video/x-ms-asf':
            case 'video/x-ms-vob':
            case 'video/x-ms-wm':
            case 'video/x-ms-wmv':
            case 'video/x-ms-wmx':
            case 'video/x-ms-wvx':
            case 'video/x-msvideo':
            case 'video/vnd.rn-realvideo':
            case 'video/x-sgi-movie':
            case 'video/x-m4v':
                return self::TYPE_VIDEO;
        }

        return null;
    }
}