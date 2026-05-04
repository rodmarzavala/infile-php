<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | FEL Environment
    |--------------------------------------------------------------------------
    | "sandbox" during development, "production" for live SAT submissions.
    */
    'environment' => env('FEL_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Emisor NIT
    |--------------------------------------------------------------------------
    | Your company NIT as registered with SAT Guatemala.
    */
    'nit' => env('FEL_NIT'),

    /*
    |--------------------------------------------------------------------------
    | Certification Flow
    |--------------------------------------------------------------------------
    | "unified"  — sign + certify in a single HTTP call (recommended).
    | "separate" — two separate HTTP calls: sign first, then certify.
    */
    'flow' => env('FEL_FLOW', 'unified'),

    /*
    |--------------------------------------------------------------------------
    | Infile Credentials
    |--------------------------------------------------------------------------
    */
    'credentials' => [
        'sign_user' => env('FEL_SIGN_USER'), // UsuarioFirma / alias prefix
        'sign_key'  => env('FEL_SIGN_KEY'),  // LlaveFirma / Token Signer from SAT
        'api_user'  => env('FEL_API_USER'),  // UsuarioApi (same as sign_user)
        'api_key'   => env('FEL_API_KEY'),   // LlaveApi provided by Infile
    ],

    /*
    |--------------------------------------------------------------------------
    | Infile API Endpoints
    |--------------------------------------------------------------------------
    | Sandbox and production share the same URLs.
    */
    'endpoints' => [
        'sign'     => 'https://signer-emisores.feel.com.gt/sign_solicitud_firmas/firma_xml',
        'certify'  => 'https://certificador.feel.com.gt/fel/certificacion/v2/dte/',
        'cancel'   => 'https://certificador.feel.com.gt/fel/anulacion/v2/dte/',
        'unified'  => 'https://certificador.feel.com.gt/fel/procesounificado/transaccion/v2/xml',
        'nit'      => 'https://consultareceptores.feel.com.gt/rest/action',
        'cui'      => 'https://certificador.feel.com.gt/api/v2/servicios/externos/cui',
        'cui_auth' => 'https://certificador.feel.com.gt/api/v2/servicios/externos/login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'times' => env('FEL_RETRY_TIMES', 3),
        'sleep' => env('FEL_RETRY_SLEEP', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contingency / Fallback
    |--------------------------------------------------------------------------
    | When enabled, failed DTEs are queued with a CAFE and retried automatically.
    */
    'fallback' => [
        'enabled' => env('FEL_FALLBACK_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | FEL Studio
    |--------------------------------------------------------------------------
    | Configure the local development studio timeline storage.
    | drivers: "sqlite" (default local file) or "database" (main db via migration)
    */
    'studio' => [
        'driver' => env('FEL_STUDIO_DRIVER', 'sqlite'),
    ],

];
