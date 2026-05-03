# Symfony

## Instalación

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

## Configuración

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
    retry:
        times: 3
        sleep: 2
    fallback:
        enabled: true
```

## Emitir desde un controlador

```php
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Item;
use InfilePhp\Core\Dte\Recipient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class FacturaController extends AbstractController
{
    #[Route('/facturas', methods: ['POST'])]
    public function emitir(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $respuesta = Invoice::create()
            ->for(
                Recipient::withTaxId($data['nit'])
                    ->name($data['nombre'])
                    ->address($data['direccion'])
            )
            ->add(Item::service($data['descripcion'])->unitPrice((float) $data['precio']))
            ->issue();

        return $this->json([
            'uuid'   => $respuesta->uuid(),
            'serie'  => $respuesta->serie(),
            'numero' => $respuesta->numero(),
        ]);
    }
}
```

## Messenger — despacho asíncrono

Despacha DTEs como mensajes de Messenger para procesarlos en segundo plano:

```php
use InfilePhp\Symfony\Messenger\IssueDteMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class FacturaController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus) {}

    public function emitirAsync(Invoice $factura): JsonResponse
    {
        $this->bus->dispatch(new IssueDteMessage($factura));

        return $this->json(['status' => 'queued']);
    }
}
```

Configura el transporte en `config/packages/messenger.yaml`:

```yaml
framework:
    messenger:
        transports:
            fel_async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
        routing:
            'InfilePhp\Symfony\Messenger\IssueDteMessage': fel_async
```

## Profiler DataCollector

La barra del Profiler de Symfony muestra automáticamente por cada request:

- Cantidad de DTEs emitidos / fallidos
- Tiempos de firma y certificación en milisegundos
- Payload XML completo (expandible)
- Errores de Infile con descripción legible

No requiere configuración adicional — se activa automáticamente en entorno `dev`.

## Health check

```php
use InfilePhp\Symfony\HealthCheck\InfileHealthCheck;

class StatusController extends AbstractController
{
    public function __construct(private InfileHealthCheck $health) {}

    #[Route('/health/fel')]
    public function status(): JsonResponse
    {
        return $this->json([
            'infile' => $this->health->isHealthy() ? 'ok' : 'down',
            'latency_ms' => $this->health->responseTimeMs(),
        ]);
    }
}
```

## Escuchar eventos del core

```php
use InfilePhp\Core\Events\DteIssued;
use InfilePhp\Core\Events\DteFailed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificacionFelSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DteIssued::class => 'onDteIssued',
            DteFailed::class => 'onDteFailed',
        ];
    }

    public function onDteIssued(DteIssued $event): void
    {
        // Notificar al cliente, actualizar ERP, etc.
    }

    public function onDteFailed(DteFailed $event): void
    {
        // Alertar al equipo de soporte
    }
}
```
