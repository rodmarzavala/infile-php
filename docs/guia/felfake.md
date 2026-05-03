# FelFake — Pruebas sin conexión real

`FelFake` elimina la dependencia con la API de Infile en tus pruebas. Disponible en los adaptadores Laravel y Symfony.

## Configuración básica

```php
use InfilePhp\Laravel\Testing\FelFake;

class FacturaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        FelFake::succeed(); // Todas las llamadas exitosas
    }
}
```

## Modos de operación

### Siempre exitoso

```php
FelFake::succeed();

Invoice::create()->forFinalConsumer()
    ->add(Item::product('Test')->unitPrice(100.00))
    ->issue();

FelFake::assertIssued(1);
```

### Siempre fallido

```php
FelFake::fail();

$this->expectException(InfileCertificationException::class);

Invoice::create()->forFinalConsumer()
    ->add(Item::product('Test')->unitPrice(100.00))
    ->issue();
```

### Fallar N veces y luego tener éxito (prueba de reintentos)

```php
FelFake::failTimes(2)->thenSucceed();

Invoice::create()->forFinalConsumer()
    ->add(Item::product('Test')->unitPrice(100.00))
    ->issue();

FelFake::assertRetries(2);
FelFake::assertIssued(1);
FelFake::assertFallbackActivated();
```

## Aserciones disponibles

```php
// ¿Cuántos DTEs se emitieron?
FelFake::assertIssued(3);

// ¿El DTE es del tipo correcto?
FelFake::assertType(DteType::FACT);

// ¿El receptor tiene el NIT correcto?
FelFake::assertRecipient('12345678');

// ¿Se activó el modo contingencia CAFE?
FelFake::assertFallbackActivated();

// ¿Se reintentó N veces?
FelFake::assertRetries(2);

// ¿Se anuló algún DTE?
FelFake::assertCancelled(1);

// ¿No se emitió nada?
FelFake::assertNothingIssued();
```

## Ejemplo completo en Laravel

```php
use InfilePhp\Core\Enums\DteType;
use InfilePhp\Laravel\Testing\FelFake;

class OrdenCompletadaTest extends TestCase
{
    public function test_emite_factura_al_completar_orden(): void
    {
        FelFake::succeed();

        $orden = Order::factory()->create(['nit' => '12345678', 'total' => 500.00]);

        $this->post("/api/ordenes/{$orden->id}/completar")
             ->assertOk();

        FelFake::assertIssued(1);
        FelFake::assertType(DteType::FACT);
        FelFake::assertRecipient('12345678');
    }

    public function test_reintenta_cuando_infile_falla_temporalmente(): void
    {
        FelFake::failTimes(2)->thenSucceed();

        $orden = Order::factory()->create(['total' => 200.00]);

        $this->post("/api/ordenes/{$orden->id}/completar")
             ->assertOk();

        FelFake::assertRetries(2);
        FelFake::assertIssued(1);
    }

    public function test_no_emite_factura_si_monto_es_cero(): void
    {
        FelFake::succeed();

        $orden = Order::factory()->create(['total' => 0.00]);

        $this->post("/api/ordenes/{$orden->id}/completar")
             ->assertOk();

        FelFake::assertNothingIssued();
    }
}
```
