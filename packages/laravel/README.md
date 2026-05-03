# Infile PHP Laravel Adapter

[![Packagist Version](https://img.shields.io/packagist/v/rodmarzavala/infile-php-laravel)](https://packagist.org/packages/rodmarzavala/infile-php-laravel)
[![PHP Version Require](https://img.shields.io/packagist/php-v/rodmarzavala/infile-php-laravel)](https://packagist.org/packages/rodmarzavala/infile-php-laravel)
[![License](https://img.shields.io/packagist/l/rodmarzavala/infile-php-laravel)](https://packagist.org/packages/rodmarzavala/infile-php-laravel)

El adaptador oficial para Laravel del SDK `infile-php` para facturación electrónica en línea (FEL) en Guatemala.

> **Nota:** Este repositorio es una división de solo lectura (read-only split) del monorepo principal `infile-php`. Por favor, envía tus *issues* y *pull requests* al [repositorio principal](https://github.com/rodmarzavala/infile-php).

## Instalación

```bash
composer require rodmarzavala/infile-php-laravel
```

Publica el archivo de configuración:

```bash
php artisan fel:install
```

## Documentación

Para acceder a la documentación completa, opciones de configuración y referencia de la API, por favor visita nuestro sitio oficial:

**👉 [Documentación Oficial (rodmarzavala.github.io/infile-php)](https://rodmarzavala.github.io/infile-php/)**

## Ejemplo de Uso

Utiliza el *Facade* intuitivo `Fel` directamente en tus controladores o *jobs*:

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
