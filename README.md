# igc2kmz PHP Wrapper

PHP wrapper for Python script [igc2kmz](https://github.com/twpayne/igc2kmz), an IGC to Google Earth converter.

[![GitHub release](https://img.shields.io/github/release/M165437/igc2kmz-php.svg)](https://github.com/M165437/igc2kmz-php/releases/latest)
[![Unstable](https://img.shields.io/badge/unstable-master-orange.svg)](https://github.com/M165437/igc2kmz-php/tree/master)
[![Build Status](https://travis-ci.org/M165437/igc2kmz-php.svg?branch=master)](https://travis-ci.org/m165437/igc2kmz-php)
[![codecov.io](http://codecov.io/github/M165437/igc2kmz-php/coverage.svg?branch=master)](http://codecov.io/github/m165437/igc2kmz-php?branch=master)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat&colorB=458979)](https://github.com/M165437/igc2kmz-php/blob/master/LICENSE.md)
[![Twitter](https://img.shields.io/badge/twitter-@M165437-blue.svg?style=flat&colorB=00aced)](http://twitter.com/M165437)

## What is igc2kmz-php?

igc2kmz-php allows you to use Python script [igc2kmz](https://github.com/twpayne/igc2kmz), an IGC to Google Earth converter, with your PHP application.

In a nutshell: you can convert IGC files to KMZ files.

[IGC](http://vali.fai-civl.org/documents/IGC-Spec_v1.00.pdf) is a Data File Standard, developed by the IGC GPS Subcommittee and the Gliding Flight Data Recorder Manufacturers (…) to facilitate the introduction of GPS technology into gliding and in particular into competition verification and the homologation of badge and record flights, using GPS, by the FAI.

[KML](https://en.wikipedia.org/wiki/Keyhole_Markup_Language) is an XML notation for expressing geographic annotation and visualization within Internet-based, two-dimensional maps and three-dimensional Earth browsers. KMZ files are zipped KML files consisting of a single root KML document and optionally any overlays, images and icons.

## Requirements

* [igc2kmz](https://github.com/twpayne/igc2kmz) Python script
* Python version 2.x, not version 3.0 (as required by igc2kmz)
* PHP 7.1 or greater

## What's what

* **igc2kmz** is a Python script to convert IGC files to KMZ files.
* **igc2kmz-php** is a PHP wrapper around the igc2kmz Python script.

## Installation

The recommended way to install igc2kmz-php is by using [composer](https://getcomposer.org):

```bash
$ composer require m165437/igc2kmz-php
```

This will install the PHP package with your application.
Please keep in mind that the Python script **igc2kmz is not included**.

### Install the Python script igc2kmz using Composer

Head over to [m165437/igc2kmz-installer](https://github.com/m165437/igc2kmz-installer).

## Usage of igc2kmz-php

1. Get an instance of the `\Igc2KmzPhp\Igc2KmzInterface` implementation, `\Igc2KmzPhp\Igc2Kmz`. Pass the path to the igc2kmz binary to the constructor.
2. Set the igc file and options on your `\Igc2KmzPhp\Igc2Kmz` instance.
3. Run your command.

```php
$igc2kmz = new Igc2Kmz('vendor/bin/igc2kmz');
```

### With Laravel

This package includes a Laravel service provider and registers itself via [Package Discovery](https://laravel.com/docs/5.6/packages#package-discovery).

Type-hint the class in a constructor or method that is resolved by Laravel's service container. It's automatically resolved and injected into the class.

```php
public function __construct(Igc2Kmz $igc2kmz)
{
    $this->igc2kmz = $igc2kmz;
}
```

If you need to change the default binary path `vendor/bin/igc2kmz`, create a config file `config/igc2kmz.php` and set the path in there.

```php
return [
    'binary' => 'alternative/path/igc2kmz'
];
```

### Code Examples

 > Keep in mind that igc2kmz-php is designed to keep its state,
 > run `\Igc2KmzPhp\Igc2KmzInterface::resetOptions` and
 > `\Igc2KmzPhp\Igc2KmzInterface::resetPhotos` to get rid of
 > the options and photos you set for the next call on the instance.

#### Convert flight.igc to output.kmz
Make sure your igc path is correct and readable, and your output path is writable.

```php
$igc2kmz
    ->igc('path/to/flight.igc')
    ->output('path/to/output.kmz')
    ->run();
```

#### Set pilot name and glider type
For individual flights you can override the pilot name and glider type (otherwise they are taken from the IGC file).

```php
$igc2kmz
    ->igc('path/to/flight.igc')
    ->output('path/to/output.kmz')
    ->pilotName('Jane Doe')
    ->gliderType('NOVA Mentor 5')
    ->run();
```

#### Add photos with comments

```php
$igc2kmz
    ->igc('path/to/flight.igc')
    ->output('path/to/output.kmz')
    ->addPhoto('https://domain.tld/photo_1.jpg', 'Comment on first image')
    ->addPhoto('https://domain.tld/photo_2.jpg', 'Comment on second image')
    ->run();
```

#### Get process before it is run

```php
$process = $igc2kmz
    ->igc('path/to/flight.igc')
    ->output('path/to/output.kmz')
    ->build();

// do stuff with the process

$igc2kmz
    ->run($process);
```

## Testing

Run the tests with:

```bash
vendor/bin/phpunit
```

## Credits

Credit goes to Tom Payne for creating [igc2kmz](https://github.com/twpayne/igc2kmz).

## Contributing

Thank you for considering contributing to this package! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

igc2kmz-php is licensed under the MIT License (MIT). Please see the [LICENSE](LICENSE.md) file for more information.
