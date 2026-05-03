<?php

declare(strict_types=1);

namespace InfilePhp\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use InfilePhp\Core\Enums\Environment;
use InfilePhp\Core\Enums\Flow;
use InfilePhp\Core\FelConfig;
use InfilePhp\Core\Http\InfileClient;
use InfilePhp\Core\InfilePhp;
use InfilePhp\Laravel\Console\InstallCommand;
use InfilePhp\Laravel\Console\RetryPendingCommand;
use InfilePhp\Laravel\Console\StatusCommand;

/**
 * Laravel service provider for infile-php.
 * Auto-discovered via composer.json extra.laravel.providers.
 */
final class FelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/felkit.php', 'felkit');

        $this->app->singleton(FelConfig::class, function (): FelConfig {
            /** @var array<string, mixed> $cfg */
            $cfg = config('felkit');

            /** @var array<string, string> $credentials */
            $credentials = $cfg['credentials'] ?? [];

            /** @var array<string, string> $endpoints */
            $endpoints = $cfg['endpoints'] ?? [];

            /** @var array<string, mixed> $retry */
            $retry = $cfg['retry'] ?? ['times' => 3, 'sleep' => 2];

            /** @var array<string, mixed> $fallback */
            $fallback = $cfg['fallback'] ?? ['enabled' => true];

            return new FelConfig(
                nit: (string) ($cfg['nit'] ?? ''),
                signUser: (string) ($credentials['sign_user'] ?? ''),
                signKey: (string) ($credentials['sign_key'] ?? ''),
                apiUser: (string) ($credentials['api_user'] ?? ''),
                apiKey: (string) ($credentials['api_key'] ?? ''),
                environment: Environment::from((string) ($cfg['environment'] ?? 'sandbox')),
                flow: Flow::from((string) ($cfg['flow'] ?? 'unified')),
                retryTimes: (int) ($retry['times'] ?? 3),
                retrySleep: (int) ($retry['sleep'] ?? 2),
                fallbackEnabled: (bool) ($fallback['enabled'] ?? true),
                endpointSign: (string) ($endpoints['sign'] ?? ''),
                endpointCertify: (string) ($endpoints['certify'] ?? ''),
                endpointCancel: (string) ($endpoints['cancel'] ?? ''),
                endpointUnified: (string) ($endpoints['unified'] ?? ''),
                endpointNit: (string) ($endpoints['nit'] ?? ''),
                endpointCui: (string) ($endpoints['cui'] ?? ''),
                endpointCuiAuth: (string) ($endpoints['cui_auth'] ?? ''),
            );
        });

        $this->app->singleton(InfileClient::class, function (): InfileClient {
            return new InfileClient($this->app->make(FelConfig::class));
        });

        // Register FEL Studio only in local or testing environments
        if ($this->app->isLocal() || $this->app->runningUnitTests()) {
            $this->app->register(\InfilePhp\Laravel\Studio\StudioServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $config = $this->app->make(FelConfig::class);

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        // Bridge PSR-14 events to Laravel's event system
        $laravelDispatcher = new \InfilePhp\Laravel\Events\LaravelEventDispatcher($dispatcher);

        InfilePhp::configure($config, $laravelDispatcher);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/felkit.php' => config_path('felkit.php'),
            ], 'felkit-config');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                InstallCommand::class,
                RetryPendingCommand::class,
                StatusCommand::class,
            ]);
        }
    }
}
