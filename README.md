# Universally Unique Lexicographically Sortable Identifier

A PHP port of [alizain/ulid](https://github.com/alizain/ulid) with some minor improvements.

[![Latest Stable Version](https://poser.pugx.org/rvdv/ulid/v/stable)](https://packagist.org/packages/rvdv/ulid)
[![Build Status](https://travis-ci.org/robinvdvleuten/php-ulid.svg?branch=master)](https://travis-ci.org/robinvdvleuten/php-ulid)

## Installation

The recommended way to install the library is through [Composer](http://getcomposer.org).

```bash
composer require rvdv/ulid
```

## Quick Example

```php
use Ulid\Ulid;

$ulid = Ulid::generate();
print $ulid; // 01b8kyr6g8bc61ce8r6k2t16hy
```

## License

MIT © [Robin van der Vleuten](https://www.robinvdvleuten.nl)

// https://github.com/PUGX/shortid-doctrine
