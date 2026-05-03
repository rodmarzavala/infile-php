# Laravel

## Instalación

```bash
composer require rodmarzavala/infile-php-laravel
php artisan fel:install
```

El comando `fel:install`:
1. Publica `config/felkit.php`
2. Ejecuta la migración que crea la tabla `fel_dte`
3. Imprime un checklist de variables `.env` con descripción de cada una

## Configuración

```php
// config/felkit.php (publicado por fel:install)
return [
    'nit'         => env('FEL_NIT'),
    'environment' => env('FEL_ENV', 'sandbox'),
    'flow'        => env('FEL_FLOW', 'unified'),
    'credentials' => [
        'sign_user' => env('FEL_SIGN_USER'),
        'sign_key'  => env('FEL_SIGN_KEY'),
        'api_user'  => env('FEL_API_USER'),
        'api_key'   => env('FEL_API_KEY'),
    ],
    'email_copy'  => env('FEL_EMAIL_COPY'),
];
```

## Emitir desde un controlador

```php
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Item;
use InfilePhp\Core\Dte\Recipient;

class FacturaController extends Controller
{
    public function emitir(Request $request): JsonResponse
    {
        $request->validate([
            'nit'       => 'required|string',
            'nombre'    => 'required|string',
            'direccion' => 'required|string',
            'items'     => 'required|array|min:1',
        ]);

        $response = Invoice::create()
            ->for(
                Recipient::withTaxId($request->nit)
                    ->name($request->nombre)
                    ->address($request->direccion)
            )
            ->add(
                Item::service($request->input('items.0.descripcion'))
                    ->unitPrice($request->input('items.0.precio'))
            )
            ->issue();

        return response()->json([
            'uuid'   => $response->uuid(),
            'serie'  => $response->serie(),
            'numero' => $response->numero(),
        ]);
    }
}
```

## Facade

```php
use InfilePhp\Laravel\Facades\Fel;

$response = Fel::certify($dte);
```

## Comandos Artisan

### `fel:install`

```bash
php artisan fel:install
```

Publica configuración y migraciones. Imprime checklist de `.env`.

### `fel:status`

```bash
php artisan fel:status

# Infile Certification Endpoint ...... OK (142 ms)
# Infile Sign Endpoint ............... OK (98 ms)
```

### `fel:retry-pending`

```bash
php artisan fel:retry-pending

# Reintentando 3 DTE(s) pendientes...
# [OK] DTE 550e8400... certificado exitosamente
```

## Log channel dedicado

Todos los eventos FEL se registran en `storage/logs/fel.log`:

```
[2024-01-15 10:32:41] fel.INFO: DTE emitido {"uuid":"550e8400","serie":"A","numero":"1234"}
[2024-01-15 10:32:55] fel.WARNING: Contingencia activada {"cafe":"A-001","reason":"Timeout"}
[2024-01-15 10:33:10] fel.INFO: Servicio Infile restaurado {"downtime_seconds":45}
```

## Eventos Laravel

Los eventos del core se traducen al sistema de eventos de Laravel automáticamente:

```php
use InfilePhp\Core\Events\DteIssued;

Event::listen(DteIssued::class, function (DteIssued $event) {
    // Enviar factura por correo, actualizar ERP, etc.
    Mail::to($cliente)->send(new FacturaMail($event->uuid));
});
```

## Integración con jobs

Para emitir facturas de forma asíncrona:

```php
// app/Jobs/EmitirFacturaJob.php
class EmitirFacturaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Order $order) {}

    public function handle(): void
    {
        Invoice::create()
            ->for(Recipient::withTaxId($this->order->nit)->name($this->order->name)->address($this->order->address))
            ->add(Item::service($this->order->description)->unitPrice($this->order->total))
            ->issue();
    }
}

// Despachar
EmitirFacturaJob::dispatch($order);
```

## Pruebas

Ver la guía completa de [FelFake](/guia/felfake).

```php
use InfilePhp\Laravel\Testing\FelFake;

class FacturaTest extends TestCase
{
    public function test_emite_factura_al_completar_orden(): void
    {
        FelFake::succeed();

        $this->post('/api/ordenes', [
            'nit'    => '12345678',
            'nombre' => 'ACME Corp',
            'total'  => 1500.00,
        ])->assertOk();

        FelFake::assertIssued(1);
        FelFake::assertRecipient('12345678');
    }
}
```
