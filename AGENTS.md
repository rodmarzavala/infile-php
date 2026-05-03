# infile-php — Agent Context

## What is this project

`infile-php` is an open source PHP SDK for Guatemala's electronic invoicing system
(FEL - Factura Electrónica en Línea) by SAT Guatemala, using Infile S.A. as the
certifying entity.

The goal is to be THE standard FEL integration for the Guatemalan PHP community —
well documented, elegantly designed, and production-ready across all major PHP
frameworks and platforms.

Repository: `rodmarzavala/infile-php`

---

## Repository structure

Monorepo managed with monorepo-builder (Symplify).

```
infile-php/
├── packages/
│   ├── core/           ← Pure PHP 8.2, zero framework dependencies
│   ├── laravel/        ← Laravel adapter (ServiceProvider, Facades, FelFake)
│   ├── symfony/        ← Symfony adapter (Bundle, Messenger, DataCollector)
│   └── wordpress/      ← WordPress plugin + WooCommerce integration
├── spec/               ← Shared contract: fixtures, error codes, XSD schemas
├── docs/               ← Unified documentation
├── AGENTS.md           ← This file
└── README.md
```

---

## Release versioning

Single version number across all packages.

| Version | Scope |
|---------|-------|
| 1.0.0   | core + laravel + FelFake + XSD validation + `fel:install` |
| 1.1.0   | symfony adapter + Profiler DataCollector |
| 1.2.0   | wordpress plugin + WooCommerce integration |
| 1.3.0   | FEL Studio (local web UI) |

---

## Core architecture principles

- `packages/core` is framework agnostic. It must NEVER depend on Laravel,
  Symfony, WordPress, or any framework. Pure PHP 8.2.
- Framework adapters only do wiring (register services, bind interfaces).
  All business logic lives in core.
- Core fires events. Adapters listen to them. This decouples everything.
- The public API must read like plain English. No abbreviations, no jargon.
- WordPress adapter is the only exception to PHP 8.2 minimum — it must support
  PHP 7.4+ to match the WordPress ecosystem. Core still requires 8.2.

---

## Public API contract (PHP reference)

```php
// Standard invoice
Invoice::create()
    ->for(Recipient::withTaxId('12345678')->name('...')->address('...'))
    ->add(Item::product('...')->quantity(2)->unitPrice(8_500.00))
    ->add(Item::service('...')->quantity(1)->unitPrice(500.00))
    ->issue();

// Final consumer (no tax ID)
Invoice::create()->forFinalConsumer()->add(...)->issue();

// Credit / Debit notes
CreditNote::create()->for($invoice)->reason('...')->issue();
DebitNote::create()->for($invoice)->reason('...')->issue();

// Small taxpayer invoice
SmallTaxpayerInvoice::create()->forFinalConsumer()->add(...)->issue();

// Cancellation
$invoice->cancel(reason: '...');

// Pre-send XSD validation (no transaction consumed)
$invoice->validate();

// NIT lookup
Rtu::lookupNit('12345678');
// → TaxpayerData { nit, name, message }

// CUI lookup (JWT managed internally, dev never sees tokens)
Rtu::lookupCui('1234567890101');
// → PersonData { cui, name, deceased }

// Testing
FelFake::succeed();
FelFake::fail();
FelFake::failTimes(2)->thenSucceed();
FelFake::assertIssued(1);
FelFake::assertType(DteType::INVOICE);
FelFake::assertRecipient('12345678');
FelFake::assertFallbackActivated();
FelFake::assertRetries(2);
FelFake::assertCancelled(1);
FelFake::assertNothingIssued();
```

---

## Infile API endpoints

### Separate flow

| Purpose     | Method | URL |
|-------------|--------|-----|
| Sign XML    | POST   | `https://signer-emisores.feel.com.gt/sign_solicitud_firmas/firma_xml` |
| Certify DTE | POST   | `https://certificador.feel.com.gt/fel/certificacion/v2/dte/` |
| Cancel DTE  | POST   | `https://certificador.feel.com.gt/fel/anulacion/v2/dte/` |

### Unified flow

| Purpose        | Method | URL |
|----------------|--------|-----|
| Sign + Certify | POST   | `https://certificador.feel.com.gt/fel/procesounificado/transaccion/v2/xml` |

### Lookup services

| Purpose    | Method | URL |
|------------|--------|-----|
| NIT lookup | POST   | `https://consultareceptores.feel.com.gt/rest/action` |
| CUI login  | POST   | `https://certificador.feel.com.gt/api/v2/servicios/externos/login` |
| CUI lookup | POST   | `https://certificador.feel.com.gt/api/v2/servicios/externos/cui` |

The SDK must support both flows. Sandbox and production use the SAME URLs.

---

## Credentials structure

```php
'credentials' => [
    'sign_user' => env('FEL_SIGN_USER'),  // UsuarioFirma / alias (prefix)
    'sign_key'  => env('FEL_SIGN_KEY'),   // LlaveFirma / Token Signer (from SAT)
    'api_user'  => env('FEL_API_USER'),   // UsuarioApi (same value as sign_user)
    'api_key'   => env('FEL_API_KEY'),    // LlaveApi (provided by Infile)
]
```

Sign payload: `llave`, `archivo` (XML base64), `codigo`, `alias`, `es_anulacion`

Certify headers: `usuario`, `llave`, `identificador`, `Content-Type: application/json`

Certify body: `nit_emisor`, `correo_copia`, `xml_dte` (signed XML base64)

---

## Transaction identifier (idempotency key)

The `identificador` is Infile's native idempotency mechanism. Same identifier sent
twice within 24h returns the original document instead of duplicating. The SDK must
generate and persist this per DTE before the first attempt. This IS the retry system.

---

## API rate limits

| Limit | Value |
|-------|-------|
| Daily API calls (implementation mode) | 2,000 |
| CUI auth logins per day | 50 |
| CUI JWT token lifetime | 2 hours |

Expose `$response->remainingCredits()`. Throw `DailyLimitExceededException` with
a clear message. Cache CUI token aggressively — 50 logins/day is very easy to exhaust.

---

## DTE types (priority order)

| Code | Name |
|------|------|
| FACT | Standard invoice |
| FCAM | Invoice with amendment |
| FPEQ | Small taxpayer invoice |
| FCAP | Small taxpayer invoice with amendment |
| FESP | Special invoice |
| NABN | Non-taxable document |
| RDON | Donation receipt |
| RECI | Receipt |
| NDEB | Debit note |
| NCRE | Credit note |

---

## Core events

All payloads are readonly classes. Never add framework-specific events in core.

```php
DteIssued(DteIssuedPayload $payload)
DteFailed(DteFailedPayload $payload)
FallbackActivated(FallbackPayload $payload)
DteCancelled(DteCancelledPayload $payload)
InfileServiceDown(ServiceDownPayload $payload)
InfileServiceRestored(ServiceRestoredPayload $payload)
```

---

## Contingency / fallback behavior

When Infile is unreachable:
1. Core generates a CAFE (local access number)
2. DTE is queued with its CAFE and idempotency key
3. Exponential backoff retry (configurable)
4. Certifies automatically when service is restored
5. Developer code does not change — fully transparent

---

## Typed exceptions hierarchy

```
InfilePhpException
├── InfileException
│   ├── InfileAuthException
│   ├── InfileServiceUnavailableException
│   ├── InfileCertificationException
│   └── DailyLimitExceededException
├── DteValidationException
│   ├── InvalidTaxIdException
│   ├── InvalidDteStructureException
│   └── XsdValidationException
└── ContingencyException
    ├── CafeGenerationException
    └── QueueException
```

Every exception carries a typed payload. No silent failures. No bare strings.

---

## PHP code standards (core, laravel, symfony)

- PHP 8.2 minimum
- `declare(strict_types=1)` in every file, no exceptions
- Readonly classes and properties where applicable
- Enums for DTE types, environments, error codes
- Named arguments in calls with 2+ parameters
- No magic methods except `__construct` where strictly unavoidable
- PSR-12 enforced by PHP CS Fixer
- PHPStan level 9 — must pass from day one
- Full PHPDoc on all public methods
- No `new` in the public API — always static named constructors
- English only: code, comments, docblocks, exception messages

---

## Laravel adapter specifics

- ServiceProvider auto-discovered via `composer.json` extra
- Facade: `Fel::issue()` as shorthand
- `php artisan fel:install` — publishes config, runs migrations, prints .env
  checklist with description for each required variable
- `php artisan fel:retry-pending` — manually retry queued DTEs
- `php artisan fel:status` — Infile health check with response time
- Automatic queue integration for contingency (zero config)
- Dedicated `fel` log channel → `storage/logs/fel.log`
- FelFake mirrors `Mail::fake()` / `Queue::fake()` pattern exactly

---

## Symfony adapter specifics

- Bundle with zero-config if env vars are set
- Messenger integration — `IssueDteMessage` + handler auto-registered
- DataCollector for Symfony Profiler toolbar:
  - Icon showing DTE count and status per request
  - Full timeline: sign → certify → response with ms timings
  - XML request/response payloads per transaction
  - Infile errors translated to human-readable Spanish
  - One-click DTE replay from the toolbar
- Health check compatible with `symfony/health-check`
- Dedicated Monolog channel for all FEL activity

---

## WordPress adapter specifics

### PHP compatibility
PHP 7.4+ (not 8.2). No enums, no readonly, no named arguments in this package.
Use arrays and constants instead. Core handles all business logic.

### Plugin structure

```
packages/wordpress/
├── infile-php.php               ← plugin entry point, headers, bootstrap
├── includes/
│   ├── Admin/
│   │   ├── SettingsPage.php     ← WP admin settings (credentials, toggles)
│   │   └── InvoiceListPage.php  ← table of issued DTEs with status
│   ├── Core/
│   │   └── InfileService.php    ← thin wrapper over core SDK
│   └── WooCommerce/
│       ├── OrderInvoiceHook.php   ← hooks woocommerce_order_status_completed
│       ├── InvoiceMetaBox.php     ← shows UUID/serie/numero in order detail
│       └── RefundCreditNote.php   ← hooks woocommerce_order_fully_refunded
├── templates/
│   └── invoice-meta-box.php
└── readme.txt                   ← WordPress plugin readme format
```

### WordPress hooks

```php
// Auto-invoice on order completion
add_action('woocommerce_order_status_completed', [OrderInvoiceHook::class, 'handle']);

// Auto credit note on full refund
add_action('woocommerce_order_fully_refunded', [RefundCreditNote::class, 'handle']);

// Manual invoice button in order detail
add_action('woocommerce_order_actions', [InvoiceMetaBox::class, 'addAction']);
```

### Admin settings page
Location: WooCommerce > FEL / Infile
Fields: FEL_SIGN_USER, FEL_SIGN_KEY, FEL_API_USER, FEL_API_KEY, NIT emisor,
email copy, flow selection, auto-invoice toggle, auto credit note toggle,
service status indicator (live Infile health check).

### WooCommerce order meta

After issuing, save to order meta:
- `_fel_uuid`
- `_fel_serie`
- `_fel_numero`
- `_fel_issued_at`
- `_fel_xml_certified` (base64)
- `_fel_status` (issued | failed | pending | cancelled)

Display in order detail and as a column in the orders list.

---

## Testing utilities (FelFake)

Available in Laravel and Symfony adapters. Must feel identical in both.

| Method | Behavior |
|--------|----------|
| `FelFake::succeed()` | All calls succeed |
| `FelFake::fail()` | All calls fail |
| `FelFake::failTimes(n)->thenSucceed()` | Fail n times then succeed |
| `FelFake::assertIssued(int $count)` | Assert n DTEs issued |
| `FelFake::assertType(DteType $type)` | Assert DTE type |
| `FelFake::assertRecipient(string $taxId)` | Assert recipient |
| `FelFake::assertFallbackActivated()` | Assert contingency triggered |
| `FelFake::assertRetries(int $count)` | Assert retry count |
| `FelFake::assertCancelled(int $count)` | Assert n DTEs cancelled |
| `FelFake::assertNothingIssued()` | Assert zero DTEs issued |

---

## FEL Studio (v1.3.0)

Local web UI — development only, never in production.

- Visual DTE builder with real-time XML preview
- XSD validation before sending (no transaction consumed)
- Full timeline: sign → certify → response with ms timings
- Infile error translator (error codes → human readable Spanish)
- DTE replay — resend any previous DTE in one click
- Interceptor mode — records real calls, converts to test fixtures

---

## Definition of done for every task

- [ ] `declare(strict_types=1)` present (core/laravel/symfony)
- [ ] PHPStan level 9 passes with zero errors (core/laravel/symfony)
- [ ] Unit tests written using fixtures from `spec/`
- [ ] All public methods have PHPDoc
- [ ] `CHANGELOG.md` updated
- [ ] README quick start example still works
- [ ] English only — no Spanish in code, comments, or exception messages