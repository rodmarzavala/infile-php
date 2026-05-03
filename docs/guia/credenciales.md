# Credenciales de Infile

## ¿Qué credenciales necesito?

Para utilizar el SDK necesitas cuatro credenciales proporcionadas por **Infile S.A.** durante el proceso de activación:

| Variable | Nombre técnico | Descripción |
|----------|---------------|-------------|
| `FEL_SIGN_USER` | UsuarioFirma / alias | Prefijo del certificado de firma digital |
| `FEL_SIGN_KEY` | LlaveFirma / Token Signer | Llave de firma digital emitida por SAT |
| `FEL_API_USER` | UsuarioApi | Mismo valor que `FEL_SIGN_USER` en la mayoría de casos |
| `FEL_API_KEY` | LlaveApi | Llave API proporcionada por Infile |

Además necesitas:

| Variable | Descripción |
|----------|-------------|
| `FEL_NIT` | NIT de tu empresa (el emisor) |
| `FEL_ENV` | `sandbox` para pruebas, `production` para producción |
| `FEL_FLOW` | `unified` (recomendado) o `separate` |

## Sandbox vs. Producción

::: info
El entorno de sandbox y producción comparten las **mismas URLs de endpoint**. La diferencia está únicamente en las credenciales. Las credenciales de sandbox son entregadas por Infile durante el proceso de implementación.
:::

## Límites de la API

| Límite | Valor |
|--------|-------|
| Transacciones diarias (modo implementación) | 2,000 |
| Autenticaciones CUI por día | 50 |
| Duración del token JWT para CUI | 2 horas |

::: warning
El SDK cachea el token JWT de CUI automáticamente durante 2 horas para no agotar el límite de 50 autenticaciones/día. No implementes tu propio mecanismo de autenticación CUI.
:::

## Configuración en cada framework

### Laravel (`.env`)

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

### Symfony (`config/packages/infile_php.yaml`)

```yaml
infile_php:
    nit: '%env(FEL_NIT)%'
    environment: '%env(FEL_ENV)%'
    flow: '%env(FEL_FLOW)%'
    credentials:
        sign_user: '%env(FEL_SIGN_USER)%'
        sign_key:  '%env(FEL_SIGN_KEY)%'
        api_user:  '%env(FEL_API_USER)%'
        api_key:   '%env(FEL_API_KEY)%'
```

### WordPress

Ve a **WooCommerce › FEL / Infile** e ingresa las credenciales en los campos del formulario. Se almacenan en `wp_options`.

### PHP puro

```php
use InfilePhp\Core\FelConfig;
use InfilePhp\Core\InfilePhp;
use InfilePhp\Core\Enums\Environment;
use InfilePhp\Core\Enums\Flow;

InfilePhp::configure(new FelConfig(
    nit: $_ENV['FEL_NIT'],
    signUser: $_ENV['FEL_SIGN_USER'],
    signKey: $_ENV['FEL_SIGN_KEY'],
    apiUser: $_ENV['FEL_API_USER'],
    apiKey: $_ENV['FEL_API_KEY'],
    environment: Environment::from($_ENV['FEL_ENV']),
    flow: Flow::from($_ENV['FEL_FLOW']),
));
```

## Contactar a Infile

Para obtener credenciales de producción, contacta directamente a [Infile S.A.](https://infile.com.gt). Este SDK es independiente de Infile — no somos afiliados ni representantes oficiales.
