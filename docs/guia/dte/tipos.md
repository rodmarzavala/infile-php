# Todos los tipos de DTE

Guatemala FEL define los siguientes tipos de Documentos Tributarios Electrónicos:

| Código | Nombre | Clase PHP |
|--------|--------|-----------|
| `FACT` | Factura estándar | `Invoice` |
| `FCAM` | Factura con enmienda | `Invoice` (próximamente) |
| `FPEQ` | Factura pequeño contribuyente | `SmallTaxpayerInvoice` |
| `FCAP` | Factura pequeño contribuyente con enmienda | `SmallTaxpayerInvoice` (próximamente) |
| `FESP` | Factura especial | `Invoice` (próximamente) |
| `NABN` | Documento no afecto | próximamente |
| `RDON` | Recibo de donación | próximamente |
| `RECI` | Recibo | próximamente |
| `NDEB` | Nota de débito | `DebitNote` |
| `NCRE` | Nota de crédito | `CreditNote` |

## FACT — Factura estándar

```php
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Item;
use InfilePhp\Core\Dte\Recipient;

Invoice::create()
    ->for(
        Recipient::withTaxId('12345678')
            ->name('ACME Corporation S.A.')
            ->address('5a Avenida 5-55 Zona 1, Ciudad de Guatemala')
    )
    ->add(Item::product('Producto')->quantity(2)->unitPrice(500.00))
    ->add(Item::service('Servicio')->quantity(1)->unitPrice(1_000.00))
    ->issue();
```

## FPEQ — Pequeño contribuyente

```php
use InfilePhp\Core\Dte\SmallTaxpayerInvoice;

SmallTaxpayerInvoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Artículo artesanal')->quantity(1)->unitPrice(150.00))
    ->issue();
```

## NCRE — Nota de crédito

```php
use InfilePhp\Core\Dte\CreditNote;

CreditNote::create()
    ->for($facturaOriginal)
    ->reason('Devolución de mercadería defectuosa')
    ->issue();
```

## NDEB — Nota de débito

```php
use InfilePhp\Core\Dte\DebitNote;

DebitNote::create()
    ->for($facturaOriginal)
    ->reason('Cargo adicional por flete no incluido en factura original')
    ->issue();
```

## Consumidor final

Todos los tipos soportan emisión a consumidor final (NIT = CF):

```php
Invoice::create()
    ->forFinalConsumer()
    ->add(Item::product('Cualquier producto')->unitPrice(100.00))
    ->issue();
```
