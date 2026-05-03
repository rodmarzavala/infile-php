# Infile PHP Laravel Adapter

[![Packagist Version](https://img.shields.io/packagist/v/rodmarzavala/infile-php-laravel)](https://packagist.org/packages/rodmarzavala/infile-php-laravel)
[![PHP Version Require](https://img.shields.io/packagist/php-v/rodmarzavala/infile-php-laravel)](https://packagist.org/packages/rodmarzavala/infile-php-laravel)
[![License](https://img.shields.io/packagist/l/rodmarzavala/infile-php-laravel)](https://packagist.org/packages/rodmarzavala/infile-php-laravel)

The official Laravel adapter for the `infile-php` Guatemala FEL (Factura Electrónica en Línea) SDK.

> **Note:** This repository is a read-only split of the main `infile-php` monorepo. Please submit issues and pull requests to the [main repository](https://github.com/rodmarzavala/infile-php).

## Installation

```bash
composer require rodmarzavala/infile-php-laravel
```

Publish the configuration file:

```bash
php artisan fel:install
```

## Documentation

For full documentation, configuration options, and API reference, please visit our official documentation site:

**👉 [Official Documentation (rodmarzavala.github.io/infile-php)](https://rodmarzavala.github.io/infile-php/)**

## Usage Example

Use the intuitive `Fel` facade directly in your controllers or jobs:

```php
use InfilePhp\Laravel\Facades\Fel;
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Recipient;
use InfilePhp\Core\Dte\Item;

$response = Fel::issue(
    Invoice::create()
        ->for(Recipient::withTaxId('12345678')->name('Juan Pérez')->address('Ciudad'))
        ->add(Item::product('Laptop')->quantity(1)->unitPrice(8500.00))
);

echo $response->uuid();
```
