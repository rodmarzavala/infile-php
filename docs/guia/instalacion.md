# Instalación

## Requisitos

| Componente | Versión mínima |
|-----------|----------------|
| PHP | 8.2 (core, Laravel, Symfony) / 7.4 (WordPress) |
| Composer | 2.x |
| Extensión `ext-json` | cualquier versión |
| Extensión `ext-mbstring` | cualquier versión |

## Laravel

```bash
composer require rodmarzavala/infile-php-laravel
```

Publica la configuración y ejecuta las migraciones:

```bash
php artisan fel:install
```

El comando imprimirá un checklist de variables de entorno. Agrégalas a tu `.env`:

```dotenv
FEL_NIT=12345678
FEL_ENV=sandbox
FEL_FLOW=unified
FEL_SIGN_USER=usuario_firma
FEL_SIGN_KEY=llave_firma_sat
FEL_API_USER=usuario_api
FEL_API_KEY=llave_api_infile
FEL_EMAIL_COPY=contabilidad@empresa.com
```

El ServiceProvider se descubre automáticamente vía `composer.json` extra.

## Symfony

```bash
composer require rodmarzavala/infile-php-symfony
```

Registra el bundle en `config/bundles.php`:

```php
return [
    // ...
    InfilePhp\Symfony\InfilePhpBundle::class => ['all' => true],
];
```

Crea el archivo de configuración:

```bash
# config/packages/infile_php.yaml
```

```yaml
infile_php:
    nit: '%env(FEL_NIT)%'
    environment: '%env(FEL_ENV)%'   # sandbox | production
    flow: unified                    # unified | separate
    credentials:
        sign_user: '%env(FEL_SIGN_USER)%'
        sign_key:  '%env(FEL_SIGN_KEY)%'
        api_user:  '%env(FEL_API_USER)%'
        api_key:   '%env(FEL_API_KEY)%'
    retry:
        times: 3
        sleep: 2
    fallback:
        enabled: true
```

## WordPress / WooCommerce

**Opción A — Composer:**

```bash
composer require rodmarzavala/infile-php-wordpress
```

**Opción B — Plugin manual:**

1. Descarga el ZIP desde [GitHub Releases](https://github.com/rodmarzavala/infile-php/releases)
2. Ve a **Plugins › Añadir nuevo › Subir plugin**
3. Activa el plugin
4. Ve a **WooCommerce › FEL / Infile** e ingresa tus credenciales

## Solo el núcleo

Si tu aplicación no usa ningún framework:

```bash
composer require rodmarzavala/infile-php-core
```

Inicializa el SDK manualmente:

```php
use InfilePhp\Core\FelConfig;
use InfilePhp\Core\InfilePhp;
use InfilePhp\Core\Enums\Environment;
use InfilePhp\Core\Enums\Flow;

InfilePhp::configure(new FelConfig(
    nit: '12345678',
    signUser: 'usuario_firma',
    signKey: 'llave_firma_sat',
    apiUser: 'usuario_api',
    apiKey: 'llave_api_infile',
    environment: Environment::Sandbox,
    flow: Flow::Unified,
));
```
