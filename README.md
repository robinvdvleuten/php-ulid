# Universally Unique Lexicographically Sortable Identifier

[![Latest Stable Version](https://poser.pugx.org/robinvdvleuten/ulid/v/stable)](https://packagist.org/packages/robinvdvleuten/ulid)
[![Build Status](https://travis-ci.org/robinvdvleuten/php-ulid.svg?branch=master)](https://travis-ci.org/robinvdvleuten/php-ulid)

A PHP port of [ulid/javascript](https://github.com/ulid/javascript) with some minor improvements.

## Installation

You can install the package via [Composer](https://getcomposer.org).

```bash
composer require robinvdvleuten/ulid
```

## Usage

```php
use Ulid\Ulid;

$ulid = Ulid::generate();
echo (string) $ulid; // 01B8KYR6G8BC61CE8R6K2T16HY

// Or if you prefer a lowercased output
$ulid = Ulid::generate(true);
echo (string) $ulid; // 01b8kyr6g8bc61ce8r6k2t16hy

// If you need the timestamp from an ULID instance
$ulid = Ulid::generate();
echo $ulid->toTimestamp(); // 1561622862
```

### Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

As it's just a simple port of JavaScript to PHP code. All credits should go to the original [ULID](https://github.com/ulid/spec) specs.

- [Robin van der Vleuten](https://github.com/robinvdvleuten)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
