# infile-php

[![PHP](https://img.shields.io/badge/PHP-≥8.2-8892BF.svg?logo=php&logoColor=white)](https://php.net)
[![Licencia: MIT](https://img.shields.io/badge/Licencia-MIT-yellow.svg)](LICENSE)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-nivel%209-brightgreen.svg)](https://phpstan.org)
[![PSR-12](https://img.shields.io/badge/código-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)
[![Packagist](https://img.shields.io/packagist/v/rodmarzavala/infile-php-core.svg)](https://packagist.org/packages/rodmarzavala/infile-php-core)
[![CI](https://github.com/rodmarzavala/infile-php/actions/workflows/quality.yml/badge.svg)](https://github.com/rodmarzavala/infile-php/actions)
[![Docs](https://img.shields.io/badge/docs-infile--php.dev-blue)](https://rodmarzavala.github.io/infile-php)

SDK PHP para el sistema de **Factura Electrónica en Línea (FEL)** de Guatemala, utilizando **Infile S.A.** como entidad certificadora.

> Pensado para ser el estándar de integración FEL en la comunidad PHP de Guatemala: bien documentado, con diseño elegante y listo para producción en Laravel, Symfony y WordPress.

---

## Índice

- [¿Por qué infile-php?](#por-qué-infile-php)
- [Instalación](#instalación)
- [Inicio rápido](#inicio-rápido)
- [Tipos de DTE soportados](#tipos-de-dte-soportados)
- [Laravel](#laravel)
- [Symfony](#symfony)
- [WordPress / WooCommerce](#wordpress--woocommerce)
- [Pruebas con FelFake](#pruebas-con-felfake)
- [Consultas RTU y CUI](#consultas-rtu-y-cui)
- [Notas y créditos](#notas--crédito)
- [Contribuciones](#contribuciones)
- [Hoja de ruta](#hoja-de-ruta)

---

## ¿Por qué infile-php?

- **Sin configuración innecesaria.** Cuatro líneas de código para emitir una factura certificada.
- **Diseñado para PHP moderno.** PHP 8.2, propiedades readonly, enums, atributos y PHPStan nivel 9.
- **Preparado para producción.** Claves de idempotencia, reintentos automáticos, modo de contingencia CAFE y validación XSD integrados.
- **Adaptadores nativos** para Laravel, Symfony y WordPress/WooCommerce.
- **Flujo transparente.** El desarrollador nunca ve tokens JWT, XML firmado ni identificadores de transacción; el SDK los gestiona internamente.
- **Código abierto.** MIT, sin restricciones de uso comercial.

---

## Instalación

Requiere PHP 8.2 o superior.

### Laravel

```bash
composer require rodmarzavala/infile-php-laravel
php artisan fel:install
```

Agrega las variables al `.env`:

```dotenv
FEL_NIT=12345678
FEL_ENV=sandbox          # sandbox | production
FEL_FLOW=unified         # unified | separate
FEL_SIGN_USER=usuario_firma
FEL_SIGN_KEY=llave_firma_sat
FEL_API_USER=usuario_api
FEL_API_KEY=llave_api_infile
FEL_EMAIL_COPY=contabilidad@empresa.com
```

### Symfony

```bash
composer require rodmarzavala/infile-php-symfony
```

Registra el bundle en `config/bundles.php`:

```php
InfilePhp\Symfony\InfilePhpBundle::class => ['all' => true],
```

Crea `config/packages/infile_php.yaml`:

```yaml
infile_php:
    nit: '%env(FEL_NIT)%'
    environment: '%env(FEL_ENV)%'
    flow: unified
    credentials:
        sign_user: '%env(FEL_SIGN_USER)%'
        sign_key:  '%env(FEL_SIGN_KEY)%'
        api_user:  '%env(FEL_API_USER)%'
        api_key:   '%env(FEL_API_KEY)%'
```

### WordPress / WooCommerce

```bash
composer require rodmarzavala/infile-php-wordpress
```

O instala el plugin directamente desde el directorio de WordPress. Luego ve a **WooCommerce › FEL / Infile** para ingresar tus credenciales.

### Solo el núcleo (sin framework)

```bash
composer require rodmarzavala/infile-php-core
```

---

## Inicio rápido

### Factura estándar (FACT)

```php
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Item;
use InfilePhp\Core\Dte\Recipient;

$response = Invoice::create()
    ->for(
        Recipient::withTaxId('12345678')
            ->name('ACME Corporation S.A.')
            ->address('5a Avenida 5-55 Zona 1, Guatemala City')
    )
    ->add(Item::product('Widget Pro Model X')->quantity(2)->unitPrice(8_500.00))
    ->add(Item::service('Installation and setup')->quantity(1)->unitPrice(500.00))
    ->issue();

echo $response->uuid;               // Certified DTE UUID
echo $response->serie;              // SAT assigned serie
echo $response->numero;             // Correlative number
echo $response->remainingCredits(); // Remaining API credits for today
```

### Consumidor final (sin NIT)

```php
Invoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Item')->quantity(1)->unitPrice(150.00))
    ->issue();
```

### Nota de crédito (NCRE)

```php
use InfilePhp\Core\Dte\CreditNote;

CreditNote::create()
    ->for($originalInvoice)
    ->reason('Product returned due to factory defect')
    ->issue();
```

### Nota de débito (NDEB)

```php
use InfilePhp\Core\Dte\DebitNote;

DebitNote::create()
    ->for($originalInvoice)
    ->reason('Price adjustment agreed with customer')
    ->issue();
```

### Pequeño contribuyente (FPEQ)

```php
use InfilePhp\Core\Dte\SmallTaxpayerInvoice;

SmallTaxpayerInvoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Handmade product')->quantity(3)->unitPrice(75.00))
    ->issue();
```

### Validación XSD sin consumir transacción

```php
// Validate the XML against SAT XSD schema before certifying.
// Does not consume API credits.
$invoice->validate();
```

### Anulación

```php
$invoice->cancel(reason: 'Billing amount error');
```

---

## Tipos de DTE soportados

| Código | Nombre |
|--------|--------|
| FACT   | Factura estándar |
| FCAM   | Factura con enmienda |
| FPEQ   | Factura pequeño contribuyente |
| FCAP   | Factura pequeño contribuyente con enmienda |
| FESP   | Factura especial |
| NABN   | Documento no afecto |
| RDON   | Recibo de donación |
| RECI   | Recibo |
| NDEB   | Nota de débito |
| NCRE   | Nota de crédito |

---

## Laravel

### Facade Fel

```php
use InfilePhp\Laravel\Facades\Fel;

$response = Fel::certify($dte);
```

### Comandos Artisan

| Comando | Descripción |
|---------|-------------|
| `php artisan fel:install` | Publica configuración, ejecuta migraciones e imprime checklist de variables `.env` |
| `php artisan fel:retry-pending` | Reintenta DTEs en cola por contingencia |
| `php artisan fel:status` | Verifica disponibilidad de Infile con tiempo de respuesta |

### Verificar estado del servicio

```bash
php artisan fel:status

# Infile Certification Endpoint ...... OK (142 ms)
# Infile Sign Endpoint ............... OK (98 ms)
```

### Canal de log dedicado

Todos los eventos FEL se registran automáticamente en `storage/logs/fel.log`.

---

## Symfony

### Despacho asíncrono con Messenger

```php
use InfilePhp\Symfony\Messenger\IssueDteMessage;

$bus->dispatch(new IssueDteMessage($invoice));
```

### Profiler DataCollector

La barra del Profiler muestra por cada request:
- Cantidad de DTEs emitidos / fallidos
- Tiempos de firma y certificación (ms)
- Payload XML completo (expandible)
- Errores de Infile con descripción legible

### Health check

```php
use InfilePhp\Symfony\HealthCheck\InfileHealthCheck;

$check = new InfileHealthCheck();
$check->isHealthy();      // bool
$check->responseTimeMs(); // int|null
```

---

## WordPress / WooCommerce

### Flujo automático

1. El cliente completa un pedido → estado cambia a **Completado**
2. El plugin emite la factura FEL automáticamente
3. El UUID, serie y número se guardan como meta del pedido
4. El cliente recibe su factura certificada

### Meta del pedido almacenada

| Meta key | Contenido |
|----------|-----------|
| `_fel_uuid` | UUID del DTE certificado |
| `_fel_serie` | Serie asignada por SAT |
| `_fel_numero` | Número correlativo |
| `_fel_issued_at` | Fecha y hora de emisión |
| `_fel_xml_certified` | XML certificado en base64 |
| `_fel_status` | `issued` \| `failed` \| `pending` \| `cancelled` |

### Acceso por código

```php
$uuid = get_post_meta($orderId, '_fel_uuid', true);
```

---

## Pruebas con FelFake

`FelFake` elimina la necesidad de conexión real con Infile en tus pruebas.

```php
use InfilePhp\Laravel\Testing\FelFake;

// All calls succeed
FelFake::succeed();

Invoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Test product')->unitPrice(100.00))
    ->issue();

FelFake::assertIssued(1);
FelFake::assertType(DteType::FACT);
FelFake::assertRecipient('CF');

// Fail N times then succeed
FelFake::failTimes(2)->thenSucceed();
FelFake::assertRetries(2);
FelFake::assertFallbackActivated();

// Assert nothing was issued
FelFake::assertNothingIssued();

// Verify cancellations
FelFake::assertCancelled(1);
```

---

## Consultas RTU y CUI

### Verificación de NIT

```php
use InfilePhp\Core\Sat\Rtu;

$taxpayer = Rtu::lookupNit('12345678');

echo $taxpayer->nit;     // 12345678
echo $taxpayer->name;    // TAXPAYER NAME
echo $taxpayer->message; // ACTIVE
```

### Verificación de CUI

```php
$person = Rtu::lookupCui('1234567890101');

echo $person->cui;      // 1234567890101
echo $person->name;     // FULL NAME
echo $person->deceased; // false
```

> El token JWT para CUI se gestiona automáticamente. El límite es 50 autenticaciones/día, el SDK cachea el token durante 2 horas.

---

## Notas y crédito

Este SDK utiliza la API de **Infile S.A.** (`feel.com.gt`) como entidad certificadora autorizada por la SAT Guatemala. Para obtener credenciales de producción, contacta directamente a [Infile](https://infile.com.gt).

El entorno de sandbox y producción comparten las mismas URLs de endpoint; la diferencia está en las credenciales entregadas por Infile.

---

## Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Haz fork del repositorio
2. Crea una rama descriptiva (`feature/anulacion-masiva`)
3. Asegúrate de que PHPStan nivel 9 pase: `vendor/bin/phpstan analyse packages/core/src --level=9`
4. Asegúrate de que el estilo de código sea correcto: `vendor/bin/php-cs-fixer fix --allow-risky=yes`
5. Abre un Pull Request con descripción clara del cambio

Revisa [CONTRIBUTING.md](CONTRIBUTING.md) para más detalles.

---

## Hoja de ruta

| Versión | Alcance |
|---------|---------|
| 1.0.0   | Core · Laravel · FelFake · Validación XSD · `fel:install` |
| 1.1.0   | Adaptador Symfony · Profiler DataCollector |
| 1.2.0   | Plugin WordPress · Integración WooCommerce |
| 1.3.0   | FEL Studio — UI web local para desarrollo |

---

**Licencia MIT** · Copyright © 2024 Rodmar Zavala · [Ver licencia](LICENSE)
