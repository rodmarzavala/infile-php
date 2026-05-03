<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use InfilePhp\Core\Http\InfileClient;

/**
 * Laravel Facade for the Infile HTTP client.
 *
 * @method static \InfilePhp\Core\Http\CertificationResponse certify(\InfilePhp\Core\Contracts\DteContract $dte)
 * @method static void cancel(string $uuid, \InfilePhp\Core\Enums\DteType $dteType, string $reason)
 * @method static int ping()
 *
 * @see \InfilePhp\Core\Http\InfileClient
 */
final class Fel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InfileClient::class;
    }
}
