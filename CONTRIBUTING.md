# Guía de contribución

¡Gracias por tu interés en mejorar infile-php! Este documento describe el proceso para contribuir al proyecto.

## Código de conducta

Este proyecto sigue el [Contributor Covenant](https://www.contributor-covenant.org/). Al participar, te comprometes a mantener un ambiente respetuoso y profesional.

## Cómo contribuir

### Reportar un problema

Abre un [issue en GitHub](https://github.com/rodmarzavala/infile-php/issues) con:
- Versión del SDK (`composer show rodmarzavala/infile-php-core`)
- Versión de PHP (`php -v`)
- Descripción del comportamiento esperado vs. el observado
- Código mínimo reproducible

### Proponer un cambio

1. Abre un issue describiendo la propuesta antes de implementarla
2. Espera retroalimentación antes de invertir tiempo de desarrollo

### Enviar un Pull Request

```bash
# 1. Haz fork y clona el repositorio
git clone https://github.com/tu-usuario/infile-php.git
cd infile-php

# 2. Instala dependencias
composer install
cd packages/core && composer install && cd ../..

# 3. Crea una rama
git checkout -b feature/mi-mejora

# 4. Desarrolla y verifica
vendor/bin/phpstan analyse packages/core/src --level=9
vendor/bin/php-cs-fixer fix --allow-risky=yes packages/core/src

# 5. Confirma y sube
git commit -m "feat: descripción clara del cambio"
git push origin feature/mi-mejora

# 6. Abre el Pull Request en GitHub
```

## Estándares de código

- **PHP 8.2** mínimo en `core`, `laravel`, `symfony`
- **PHP 7.4** mínimo en `wordpress` (sin enums, readonly ni argumentos nombrados)
- `declare(strict_types=1)` en todos los archivos sin excepción
- **PHPStan nivel 9** — cero errores
- **PSR-12** — enforced por PHP CS Fixer
- PHPDoc completo en todos los métodos públicos
- Inglés en código, comentarios y mensajes de excepción
- Sin `new` en la API pública — siempre constructores estáticos nombrados

## Estructura de commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: agrega soporte para NABN
fix: corrige parsing de UUID en respuesta unificada
docs: agrega ejemplo de nota de crédito
test: agrega fixture NDEB para pruebas unitarias
refactor: extrae buildResponse a método privado
chore: actualiza phpstan a 1.13
```

## Ejecutar el pipeline completo localmente

```bash
# PHPStan nivel 9 en core
vendor/bin/phpstan analyse packages/core/src --level=9

# Estilo de código
vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes packages/

# Pruebas unitarias (cuando estén implementadas)
cd packages/core && vendor/bin/phpunit
```
