AssetDistributor
================

AssetDistributor is a PHP component that can publish digital assets (video, audio...) across multiple services.

Currently, AssetDistributor can publish videos across YouTube, Vimeo and DailyMotion.
Feel free to help and integrate new Adapters for new services.


### Vocabulary

  * `Asset` — describes a digital media of one of the following type : `audio`, `document`, `image` or `video`.

  * `Adapter` — implements a `service` to `upload`, `update` or `delete` `Asset` objetcs from.

  * `AdapterCollection` — contains multiple `Adapter` objects and is traversable

  * `Owner` — handles an `AdapterCollection` and the corresponding `services` account credentials.


### Installation

The component may be added through composer :

    composer require libcast/assetdistributor


### Configuration

All your application oAuth credentials or any other configuration must be stored in a PHP configuration file.
You may have a look on `example/configuration.ini` as an example.


### Usage

First create an `Owner` called "me" to bear accounts credentials and an `AdapterCollection`
```php
$cache = new \Doctrine\Common\Cache\FilesystemCache('/tmp'); // Doctrine Cache is a dependency
$owner = new Owner('me', $cache);
```

Then create an `Asset` from an existing file
```php
$asset = AssetFactory::build(
    "$root/tests/video.mp4",        // path to file of a Flysystem\File object
    'My Video',                     // optional title
    'This is an awesome video',     // optional description
    ['test', 'asset-distributor']   // optional array of tags
);
$asset->setVisibility('private');
```

You may create an `AdapterCollection` manually:
```php
/** @var string $configPath Path to the PHP configuration file */

$adapters = new AdapterCollection;
$adapters[] = new YouTubeAdapter($owner, $configPath);
$adapters[] = new VimeoAdapter($owner, $configPath);
```

You also can retrieve the `AdapterCollection` from the cache:
```php
$adapters = AdapterCollection::retrieveFromCache($owner, $configPath);
```

Or you can create an `AdapterCollection` based on the `Asset` :
```php
$adapters = AdapterCollection::buildForAsset($asset, $owner, $configPath);
```

Once created, the `AdapterCollection` must be affiliated to the `Owner`
```php
$owner->setAdapters($adapters);
```

At this point, you may manipulate your `Asset` across all services like this:
```php
// Upload the Asset on all services
$owner->upload($asset);

$asset->setTitle('Different title');
$asset->addTag('foobar');

// Update the Asset on all services
$owner->update($asset);

// Delete the Asset on all services
$owner->delete($asset);
```
