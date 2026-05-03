# Consultas RTU y CUI

## Verificación de NIT (RTU)

Consulta el Registro Tributario Unificado de SAT Guatemala para verificar si un NIT existe y está activo antes de emitir una factura.

```php
use InfilePhp\Core\Sat\Rtu;

$contribuyente = Rtu::lookupNit('12345678');

echo $contribuyente->nit;     // 12345678
echo $contribuyente->name;    // NOMBRE COMPLETO DEL CONTRIBUYENTE
echo $contribuyente->message; // ACTIVO | CANCELADO | NO EXISTE
```

### Caso de uso: validar antes de facturar

```php
$nit = $request->input('nit');

try {
    $contribuyente = Rtu::lookupNit($nit);

    if ($contribuyente->message !== 'ACTIVO') {
        return response()->json([
            'error' => "El NIT {$nit} no está activo en el RTU de SAT."
        ], 422);
    }

    $response = Invoice::create()
        ->for(
            Recipient::withTaxId($nit)
                ->name($contribuyente->name)
                ->address($request->input('direccion'))
        )
        ->add(Item::service('Servicio')->unitPrice($request->input('total')))
        ->issue();

} catch (\InfilePhp\Core\Exceptions\InvalidTaxIdException $e) {
    return response()->json(['error' => 'NIT inválido'], 422);
}
```

## Verificación de CUI

Consulta el CUI (Código Único de Identificación) de una persona natural. Requiere credenciales CUI configuradas en Infile.

```php
$persona = Rtu::lookupCui('1234567890101');

echo $persona->cui;      // 1234567890101
echo $persona->name;     // NOMBRE APELLIDO
echo $persona->deceased; // false
```

::: warning Límite de autenticaciones CUI
El servicio CUI permite máximo **50 autenticaciones JWT por día**. El SDK cachea el token automáticamente durante 2 horas para minimizar el consumo. **No implementes tu propio manejo de tokens CUI.**
:::

### Verificar si una persona está fallecida

```php
$persona = Rtu::lookupCui($cui);

if ($persona->deceased) {
    throw new \RuntimeException('No se puede emitir DTE a CUI de persona fallecida.');
}
```
