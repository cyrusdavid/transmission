PHP Transmission RPC Client
===

A fully-tested PHP JSON-RPC client library for [Transmission](https://transmissionbt.com)

[![Build Status](https://secure.travis-ci.org/vohof/transmission.png)](http://travis-ci.org/vohof/transmission)

## Table of Contents
 - [Installation](#installation)
 - [Usage](#example-usage)
 - [Usage with Laravel](#use-transmission-with-laravel)
 - [Advanced](#advanced)
 - [To-Do](#to-do)
 - [License](#license)

## Installation

Install through [Composer](https://getcomposer.org):

```json
{
  "require": {
    "vohof/transmission": "1.0.*"
  }
}
```

## Example Usage

```php
$config = array(
    'host'     => 'http://127.0.0.1',
    'endpoint' => '/transmission/rpc',
    'username' => 'foo', // Optional
    'password' => 'bar' // Optional
);

$transmission = new Vohof\Transmission($config);

// Add a torrent
$torrent = $transmission->add('magnet:?xt=urn:btih:335990d615594b9be409ccfeb95864e24ec702c7&dn=Ubuntu+12.10+Quantal+Quetzal+%2832+bits%29&tr=udp%3A%2F%2Ftracker.openbittorrent.com%3A80&tr=udp%3A%2F%2Ftracker.publicbt.com%3A80&tr=udp%3A%2F%2Ftracker.istole.it%3A6969&tr=udp%3A%2F%2Ftracker.ccc.de%3A80&tr=udp%3A%2F%2Fopen.demonii.com%3A1337');

// or
$content = base64_encode(file_get_contents('MyTorrent.torrent'));
$torrent = $transmission->add($content, true);

// Stop a torrent
$transmission->action('stop', $torrent['id']);

// Limit download speed
$transmission->set($torrent['id'], array('downloadLimit' => 100));

// Get torrent size
$transmission->get($torrent['id'], array('totalSize'));

// Remove torrent
$transmission->remove($torrent['id']));

// Remove torrent and its files
$transmission->remove($torrent['id'], true);

// Stats
$transmission->getStats();
```

See the tests for more usage

## Use Transmission with Laravel

Add the service provider and alias the package in `config/app.php`

```php
'providers' => array(
    ...
    'Vohof\TransmissionServiceProvider'
),
'aliases' => array(
    ...
    'Transmission' => 'Vohof\TransmissionFacade'
)
```

Publish config and modify `app/config/packages/transmission/config.php`

```
$ php artisan config:publish transmission --path=vendor/vohof/transmission/src/config
```

Use the library:

```php
Transmission::add($base64EncodedTorrent, true);
Torrent::stats();
```

## Advanced

The library uses [Guzzle](http://github.com/guzzle/huzzle) as it's HTTP Client but you can choose to swap it with something else if you want (eg. [Buzz](https://github.com/kriswallsmith/Buzz))

```php
class BuzzClient extends \Vohof\ClientAbstract {
    ...
}

$transmission = new Vohof\Transmission($config, new BuzzClient);
```

## To-Do

- torrent-rename-path, blocklist-update

## License

See [LICENSE](LICENSE)

