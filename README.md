# clue/qdatastream

[![CI status](https://github.com/clue/php-qdatastream/workflows/CI/badge.svg)](https://github.com/clue/php-qdatastream/actions)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/qdatastream?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/qdatastream)

Lightweight PHP library that allows exchanging binary data with Qt programs (QDataStream)

**Table of contents**

* [Usage](#usage)
  * [Writer](#writer)
  * [Reader](#reader)
  * [QVariant](#qvariant)
  * [Types](#types)
* [Install](#install)
* [Tests](#tests)
* [License](#license)

## Usage

### Writer

The `Writer` class can be used to build a binary buffer string from the
structured data you write to it.

```php
$user = new stdClass;
$user->id = 10;
$user->active = true;
$user->name = 'Alice';

$writer = new Writer();
$writer->writeUInt($user->id);
$writer->writeBool($user->active);
$writer->writeQString($user->name);

$data = (string)$writer
file_put_contents('user.dat', $data);
```

See the [class outline](src/Writer.php) for more details.

### Reader

The `Reader` class can be used to read data from a binary buffer string.

```php
$data = file_get_contents('user.dat');
$reader = new Reader($data);

$user = new stdClass();
$user->id = $reader->readUInt();
$user->active = $reader->readBool();
$user->name = $reader->readQString();
```

See the [class outline](src/Reader.php) for more details.

### QVariant

The `QVariant` class can be used to encapsulate any kind of data with an
explicit data type. When writing a `QVariant` to a buffer, it will also
include its data type so that reading it back in can be done automatically
without having to know the data type in advance.

```php
$variant = new QVariant(100, Types::TYPE_USHORT);

$writer = new Writer();
$writer->writeQVariant($variant);

$data = (string)$writer;
$reader = new Reader($data);
$value = $reader->readQVariant();

assert($value === 100);
```

Additionally, you can use the `writeQVariant()` method without having to specify
the data type in advance and let this class "guess" an appropriate data
type for serialization and add this type information to the serialized data.
This works similar to JSON encoding and accepts primitive values of type `int`,
`bool`, `string`, `null` and nested structures or type `array` or instances of `stdClass`:

```php
$rows = [
    (object)[
        'id' => 10,
        'name' => 'Alice',
        'active' => true,
        'groups' => ['admin', 'staff']
    ]
];

$writer = new Writer();
$writer->writeQVariant($rows);

$data = (string)$writer;
$reader = new Reader($data);
$values = $reader->readQVariant();

assert(count($values) === 1);
assert($values[0]->id === 10);
assert($values[0]->groups[0] === 'admin');
```

Note that associative arrays and `stdClass` objects be will serialized as a "map",
while vector arrays will be serialized as a "list". In order to avoid confusion
between these similar data structures, using instances of `stdClass` is considered
the preferred method in this project.

See the [class outline](src/QVariant.php) for more details.

### Types

The `Types` class exists to work with different data types and offers a number
of public constants to work with explicit data types and is otherwise mostly
used internally only.

See the [class outline](src/Types.php) for more details.

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require clue/qdatastream:^0.8
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 8+.
It's *highly recommended to use PHP 7+* for this project.

The `QString` and `QChar` types use the `ext-mbstring` for converting
between different character encodings.
If this extension is missing, then this library will use a slighty slower Regex
work-around that should otherwise work equally well.
Installing `ext-mbstring` is highly recommended.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

This project is released under the permissive [MIT license](LICENSE).

> Did you know that I offer custom development services and issuing invoices for
  sponsorships of releases and for contributions? Contact me (@clue) for details.
