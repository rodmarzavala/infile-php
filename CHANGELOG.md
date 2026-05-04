# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/) y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [1.3.2] - 2026-05-04

### Agregado
- **Tests — cobertura ampliada**: De 9 → 82 tests (153 assertions) en `packages/core`. Nuevos suites:
  - `RecipientTest` — `withTaxId`, `finalConsumer`, fluent setters, `isFinalConsumer`
  - `InvoiceTest` — builder, `getType`, totales, `validate()` guards, cancel sin emisión previa
  - `CreditNoteTest` — tipo, `for()`, `reason()`, delegación a factura origen, 3 guards de validación
  - `DebitNoteTest` — espejo de CreditNote para NDEB
  - `SmallTaxpayerInvoiceTest` — FPEQ, mismo patrón de builder y guards
  - `FelConfigTest` — credenciales, todos los defaults (sandbox, unified, 3 retries, fallback), endpoints
  - `CertificationResponseTest` — propiedades, `remainingCredits()`, default `issuedAt`
  - `IdempotencyKeyTest` — formato UUID4, unicidad en 50 generaciones, longitud 36

## [1.3.1] - 2026-05-04

### Corregido
- **WordPress — Emisión de facturas**: Corregido el método `OrderInvoiceHook::issueInvoice()` que llamaba a métodos inexistentes (`getUuid()`, `getSerie()`, `getNumero()`) en `Invoice`. Ahora utiliza correctamente las propiedades del `CertificationResponse` que devuelve `issue()`.
- **WordPress — Lista de facturas**: Reemplazada la consulta SQL cruda a `wp_posts` (incompatible con HPOS de WooCommerce) por `wc_get_orders()` con `meta_query`. Las facturas emitidas ahora aparecen correctamente en WooCommerce > FEL Invoices.
- **WordPress — Seguridad**: Los campos `LlaveFirma` y `LlaveApi` en la página de configuración ahora se renderizan como `type="password"` con `autocomplete="new-password"` para evitar exposición visual y autocompletado de credenciales.
- **WordPress — Dependencias PSR-7**: Reemplazada la implementación custom de PSR-7/PSR-17 (incompatible con `psr/http-message: ^2.0`) por `nyholm/psr7`, que soporta nativamente PSR-7 v1 y v2 en PHP 7.4+.

### Eliminado
- **WordPress — FEL Studio**: Removido el submenú y la integración de FEL Studio del plugin de WordPress. La SPA de React es incompatible con el sistema de assets de WordPress (conflicto `type="module"` / CORS / CSP). FEL Studio sigue disponible en los adaptadores Laravel y Symfony donde tiene sentido como herramienta de desarrollo. El plugin queda más ligero sin dependencias de Node.js o React.

## [1.3.0] - 2026-05-04
### Agregado
- **FEL Studio**: Interfaz web local para desarrollo y depuración (Timeline, Builder).
- **Seguridad en Studio**: Implementación de Gate de autorización (`viewFelStudio`) para acceso controlado en entornos no locales, y protección nativa CSRF para la SPA en React.
- **Persistencia Flexible**: Soporte nativo de base de datos (`database` driver) mediante migraciones para el Timeline de eventos, manteniendo retrocompatibilidad con SQLite.
- **Alertas Proactivas de Contingencia**: Advertencias al usuario en comandos `fel:install` y `fel:status` si el modo contingencia (`fallback`) está activo pero el driver de colas es `sync`.

## [1.2.0] - 2026-05-03
### Agregado
- **Integración PSR-18, PSR-17 y PSR-7**: El núcleo del SDK ahora es completamente agnóstico al cliente HTTP, eliminando la dependencia dura de Guzzle (`guzzlehttp/guzzle`).
- **Adaptadores de Frameworks Actualizados**:
  - `laravel`: Inyecta la factoría HTTP nativa y de Guzzle hacia `InfilePhp::configure()`.
  - `symfony`: Registra y auto-descubre las implementaciones PSR a través del Contenedor de Inyección de Dependencias.
  - `wordpress`: Utiliza una implementación custom ligera de PSR-18 usando `wp_remote_request` para lograr un peso mínimo de dependencias.
- **Tipado Estricto (PHPStan Level 9)**: Las resoluciones de tipos (`mixed`) fueron limpiadas en la inyección de configuración de Symfony para pasar los estándares de análisis estático.

## [1.0.9] - 2026-05-03
### Modificado
- Eliminación de la palabra "oficial" en la documentación y READMEs para evitar temas legales, dejando claro que es un SDK "open-source" y "nativo" comunitario.

## [1.0.8] - 2026-05-03
### Modificado
- Traducción de los README de sub-paquetes al español profesional.

## [1.0.7] - 2026-05-03
### Agregado
- Archivos README dedicados para cada sub-paquete (Core, Laravel, Symfony) para publicación en Packagist.

## [1.0.6] - 2026-05-03

### Agregado
- Paquete `core` con API fluida para emitir FACT, NCRE, NDEB y FPEQ
- Cliente HTTP con Guzzle para flujo unificado y separado
- Idempotencia automática con `ramsey/uuid`
- Consultas RTU (NIT) y CUI con caché de token JWT
- Jerarquía completa de excepciones tipadas
- Seis eventos PSR-14 para el ciclo de vida del DTE
- Paquete `laravel` con ServiceProvider, Facade, FelFake y comandos Artisan
- Paquete `symfony` con Bundle, Messenger, DataCollector y EventSubscriber
- Paquete `wordpress` con plugin WP y WooCommerce (PHP 7.4+)
- Directorio `spec/` con fixtures de prueba y códigos de error Infile
- Documentación completa en VitePress
- Pipeline CI con PHPStan nivel 9, CS Fixer, CodeQL y auditoría Composer
- Workflow de publicación automática a GitHub Pages
