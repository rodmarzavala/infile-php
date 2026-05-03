# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/) y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [1.0.5] - 2026-05-03

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
