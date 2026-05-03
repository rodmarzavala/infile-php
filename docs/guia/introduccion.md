# ¿Qué es infile-php?

**infile-php** es un SDK PHP de código abierto para integrar el sistema de **Factura Electrónica en Línea (FEL)** de Guatemala utilizando **Infile S.A.** como entidad certificadora autorizada por la SAT.

## El problema que resuelve

Integrar FEL en Guatemala implica:

- Gestionar firmas digitales XML con el servicio de firma de Infile
- Construir el XML del DTE según el esquema XSD de SAT
- Manejar dos flujos distintos de certificación (unificado y separado)
- Implementar claves de idempotencia para evitar duplicados
- Gestionar tokens JWT para consultas CUI con límite de 50/día
- Manejar contingencias cuando el servicio de Infile no está disponible

**infile-php** encapsula toda esta complejidad. El desarrollador emite facturas certificadas sin escribir una línea de XML ni gestionar tokens.

## Arquitectura

El proyecto es un **monorepo** con cuatro paquetes independientes:

| Paquete | Descripción | PHP mínimo |
|---------|-------------|------------|
| `infile-php-core` | Lógica pura sin dependencias de framework | 8.2 |
| `infile-php-laravel` | ServiceProvider, Facade, FelFake, comandos Artisan | 8.2 |
| `infile-php-symfony` | Bundle, Messenger, DataCollector | 8.2 |
| `infile-php-wordpress` | Plugin WP + integración WooCommerce | 7.4 |

## Diseño

- El **core** es framework-agnostic. Nunca depende de Laravel, Symfony ni WordPress.
- Los **adaptadores** solo realizan el cableado (registrar servicios, enlazar interfaces).
- El core **emite eventos**. Los adaptadores los escuchan. Esto desacopla todo.
- La API pública se lee como inglés simple. Sin abreviaturas, sin jerga.

## Siguiente paso

Continúa con la [instalación](/guia/instalacion) o ve directamente al [inicio rápido](/guia/inicio-rapido).
