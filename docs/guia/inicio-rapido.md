# Inicio rápido

Este ejemplo cubre los casos de uso más comunes. Para instalación y configuración, consulta la [guía de instalación](/guia/instalacion).

## Factura estándar a empresa con NIT

```php
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Item;
use InfilePhp\Core\Dte\Recipient;

$response = Invoice::create()
    ->for(
        Recipient::withTaxId('12345678')
            ->name('ACME Corporation S.A.')
            ->address('5a Avenida 5-55 Zona 1, Ciudad de Guatemala')
    )
    ->add(Item::product('Laptop Dell XPS 15')->quantity(1)->unitPrice(12_500.00))
    ->add(Item::service('Configuración inicial')->quantity(1)->unitPrice(500.00))
    ->issue();

// Datos del DTE certificado
echo $response->uuid();             // ej: 550e8400-e29b-41d4-a716-446655440000
echo $response->serie();            // ej: A
echo $response->numero();           // ej: 1234
echo $response->remainingCredits(); // créditos API restantes hoy
```

## Factura a consumidor final (sin NIT)

Cuando el cliente no tiene NIT o no lo proporciona:

```php
Invoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Zapatos deportivos talla 42')->quantity(1)->unitPrice(350.00))
    ->add(Item::product('Calcetines deportivos')->quantity(3)->unitPrice(25.00))
    ->issue();
```

## Múltiples productos y servicios

```php
Invoice::create()
    ->for(
        Recipient::withTaxId('98765432')
            ->name('Distribuidora El Sol S.A.')
            ->address('Calzada Roosevelt 15-85 Zona 7')
    )
    ->add(Item::product('Monitor 27" 4K')->quantity(2)->unitPrice(3_200.00))
    ->add(Item::product('Teclado mecánico')->quantity(2)->unitPrice(450.00))
    ->add(Item::product('Mouse inalámbrico')->quantity(2)->unitPrice(180.00))
    ->add(Item::service('Instalación en sitio')->quantity(1)->unitPrice(800.00))
    ->add(Item::service('Garantía extendida 2 años')->quantity(2)->unitPrice(350.00))
    ->issue();
```

## Nota de crédito por devolución

```php
use InfilePhp\Core\Dte\CreditNote;

$nota = CreditNote::create()
    ->for($facturaOriginal)   // DteContract de la factura emitida
    ->reason('Cliente devuelve producto por defecto de fábrica — sin uso')
    ->issue();
```

## Nota de débito por ajuste de precio

```php
use InfilePhp\Core\Dte\DebitNote;

DebitNote::create()
    ->for($facturaOriginal)
    ->reason('Ajuste por diferencia de tipo de cambio USD/GTQ pactado')
    ->issue();
```

## Pequeño contribuyente

```php
use InfilePhp\Core\Dte\SmallTaxpayerInvoice;

SmallTaxpayerInvoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Tortillas artesanales (100 unidades)')->quantity(5)->unitPrice(15.00))
    ->issue();
```

## Verificar NIT antes de facturar

```php
use InfilePhp\Core\Sat\Rtu;

$nit = '12345678';
$contribuyente = Rtu::lookupNit($nit);

if ($contribuyente->message !== 'ACTIVO') {
    throw new \RuntimeException("NIT {$nit} no está activo en el RTU de SAT.");
}

// Procede a facturar con datos verificados
Invoice::create()
    ->for(
        Recipient::withTaxId($nit)
            ->name($contribuyente->name)
            ->address('Dirección del cliente')
    )
    ->add(Item::service('Consultoría')->unitPrice(5_000.00))
    ->issue();
```

## Validar DTE sin consumir transacción

```php
$factura = Invoice::create()
    ->for(Recipient::withTaxId('12345678')->name('Test')->address('GT'))
    ->add(Item::product('Prueba')->unitPrice(100.00));

// Valida el XML contra el XSD de SAT — no consume créditos API
try {
    $factura->validate();
    echo 'DTE válido, listo para certificar.';
} catch (\InfilePhp\Core\Exceptions\XsdValidationException $e) {
    echo 'Error de validación: ' . $e->getMessage();
}

// Certifica solo si la validación pasa
$factura->issue();
```

## Anular un DTE certificado

```php
$factura->cancel(reason: 'Error en el NIT del receptor — se emitirá un nuevo DTE');
```

## Manejo de excepciones

```php
use InfilePhp\Core\Exceptions\DailyLimitExceededException;
use InfilePhp\Core\Exceptions\InfileAuthException;
use InfilePhp\Core\Exceptions\InfileCertificationException;
use InfilePhp\Core\Exceptions\InfileServiceUnavailableException;

try {
    $response = Invoice::create()
        ->for(Recipient::withTaxId('12345678')->name('Cliente')->address('GT'))
        ->add(Item::service('Servicio')->unitPrice(1_000.00))
        ->issue();

} catch (DailyLimitExceededException $e) {
    // Límite de 2,000 transacciones/día alcanzado
    logger()->critical('Límite FEL alcanzado', ['exception' => $e]);

} catch (InfileAuthException $e) {
    // Credenciales incorrectas (ERROR_401 / ERROR_403)
    logger()->error('Error de autenticación FEL', ['exception' => $e]);

} catch (InfileServiceUnavailableException $e) {
    // Infile no responde — el SDK activa modo contingencia CAFE
    logger()->warning('Infile no disponible, DTE en cola', ['endpoint' => $e->endpoint]);

} catch (InfileCertificationException $e) {
    // Error en la certificación (código de error de Infile)
    logger()->error('Error de certificación', [
        'code'    => $e->infileCode,
        'message' => $e->getMessage(),
    ]);
}
```

## Siguiente paso

- [Tipos de DTE disponibles](/guia/dte/tipos)
- [Integración con Laravel](/guia/laravel)
- [Pruebas con FelFake](/guia/felfake)
