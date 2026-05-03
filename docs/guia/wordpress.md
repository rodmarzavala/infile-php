# WordPress / WooCommerce

## Instalación

### Opción A — Compositor

```bash
composer require rodmarzavala/infile-php-wordpress
```

### Opción B — Plugin manual

1. Descarga el ZIP desde [GitHub Releases](https://github.com/rodmarzavala/infile-php/releases)
2. Ve a **Plugins › Añadir nuevo › Subir plugin**
3. Activa el plugin

## Configuración

Ve a **WooCommerce › FEL / Infile** e ingresa tus credenciales Infile y el NIT emisor.

## Flujo automático

Cuando un pedido pasa a estado **Completado**:

1. El plugin construye el DTE con los datos del pedido
2. Certifica con Infile
3. Guarda UUID, serie y número como meta del pedido

## Meta del pedido almacenada

```php
$uuid   = get_post_meta($orderId, '_fel_uuid', true);
$serie  = get_post_meta($orderId, '_fel_serie', true);
$numero = get_post_meta($orderId, '_fel_numero', true);
$status = get_post_meta($orderId, '_fel_status', true);
// status: issued | failed | pending | cancelled
```

## NIT del cliente — campo de checkout

```php
// En functions.php de tu tema
add_action('woocommerce_billing_fields', function ($fields) {
    $fields['billing_nit'] = [
        'label'    => 'NIT (opcional)',
        'required' => false,
        'class'    => ['form-row-wide'],
        'priority' => 25,
    ];
    return $fields;
});

add_action('woocommerce_checkout_update_order_meta', function ($orderId) {
    if (!empty($_POST['billing_nit'])) {
        update_post_meta($orderId, '_billing_nit', sanitize_text_field($_POST['billing_nit']));
    }
});
```

El plugin buscará `_billing_nit`. Si está vacío o es `CF`, factura a consumidor final.

## Nota de crédito automática

Al reembolso completo del pedido, el plugin emite automáticamente una nota de crédito (NCRE).
