PHP AssetDistribution component
===========================

PHP component that uploads and manage media from remote social/content platform. 

Currently handle video for YouTube.

Please be free to integrate other media/platforms (Vimeo, Scribd, SoundCloud...) 

Vocabulary:

  * a `Media` defines the file (video, audio, document...) and its metadata. By 
    itself, the media is not related to any platform. Therefore, a `Platform` object 
    or collection has to be affected to a media to enable its corresponding 
    management.

  * `Platform` are composed of `Credentials` and a `Manager`. Platforms also store
    settings (static metadata such as webservice endpoints) and parameters (user 
    dependant informations such as authentication token). 

    When serialized, platform objects describe all parameters (settings should be 
    statically described inside their specialized class or coming from a setter
    so they can't be persisted eg. \Libcast\AssetDistribution\Platform\YoutubePlatform).
    This way, `Platform` objects can be persisted for future use.

  * `PlatformCollection` serializable collection of `Platform` objects.

  * `Credentials` objects handle component authentication on its corresponding 
    platform on behalf of a user. Users can be redirected on a dedicated login page
    and asked for aproval before any credential are given to the component.

  * `Manager` objects are used from `Media` to handle the following methods:
    - **save()** uploads the file if the `Media` is new or persists the media's 
      metadata otherwise
    - **update()** edit the media from the remote platform
    - **upload()** transfers the file on the remote platform
    - **delete()** removes the media from the remote platform
    - **find( _$key_ )** search the media based on a `$key`


Install
-------

Use composer to install the composent dependancies :

    cd /path/to/composent
	php composer.phar install


Use it
------

Here is how you set up a platform:
```php
use Libcast\AssetDistribution\Platform\YoutubePlatform;

$youtube = new YoutubePlatform(array(
    'client_id' => '111222333444-a1b2c3d4e5.googleusercontent.com',
    'client_secret' => '1a2b3c4d5e6f7g8h9i0j1k2l',
    'redirect_uri' => 'http://localhost:8888/path/to/oauthcallback',
));

// Alternatively, you can use a '.ini' file to group all your platforms configuration in a single place. Here is how you submit your configuration then:
$youtube = new YoutubePlatform('/path/to/config.ini');
```

Here is how you declare your settings in a *.ini* configuration file:
```php
[youtube]
client_id = "111222333444-a1b2c3d4e5.googleusercontent.com"
client_secret = "1a2b3c4d5e6f7g8h9i0j1k2l"
redirect_uri = "http://localhost:8888/path/to/oauthcallback"
```

You also may want to declare some parameters like a refresh_token to avoid user login each time you need to manage its media:
```php
$youtube = new YoutubePlatform('/path/to/config.ini', array(
    'refresh_token' => '1/A1Bc2dE3Fg4h-I5Jk6lM7No8pQ9Rs0tU1Vw2x',
));
```

You can add several platforms to a collection (eg. a same video must be sent to many YouTube accounts):
```php
use Libcast\AssetDistribution\Platform\PlatformCollection;

$youtube1 = new YoutubePlatform(array(
    'client_id' => '111222333444-a1b2c3d4e5.googleusercontent.com',
    'client_secret' => '1a2b3c4d5e6f7g8h9i0j1k2l',
    'redirect_uri' => 'http://localhost:8888/path/to/oauthcallback',
), array(
    'refresh_token' => '1/A1Bc2dE3Fg4h-I5Jk6lM7No8pQ9Rs0tU1Vw2x',
));

$youtube2 = new YoutubePlatform('/path/to/config.ini', array(
    'refresh_token' => '1/I5Jk6lM7No8p-A1Bc2dE3Fg4hQ9Rs0tU1Vw2x',
));

$collection = new PlatformCollection;
$collection[] = $youtube1;
$collection[] = $youtube2;
```

Then all you have to do is to create a `Media` object and inject either the `Platform` or a `PlatformCollection` so that you can trigger on of the manager's methods like **save()** to manipulate your files on their corresponding platforms:
```php
use Libcast\AssetDistribution\Media\Media;

$video = Media::load('/path/to/a/video.mp4', $collection);
$video->save();
```


Complete example
----------------

```php
<?php

require '/path/to/vendor/autoload.php';

use Libcast\AssetDistribution\Platform\YoutubePlatform;
use Libcast\AssetDistribution\Media\Media;

$youtube = new YoutubePlatform('/path/to/config.ini', array(
    'refresh_token' => '1/I5Jk6lM7No8p-A1Bc2dE3Fg4hQ9Rs0tU1Vw2x',
));

$video = Media::load('/path/to/a/video.mp4', $youtube);
$video
    ->setParameter('title',       'Title of my awesome video!')
    ->setParameter('description', 'This video is pure awesomeness! You have to watch it xD')
    ->setParameter('keywords',    array('awesome', 'fun'))
    ->setParameter('category',    22)
    ->save();
```
