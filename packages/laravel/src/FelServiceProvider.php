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
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
                nit: is_string($cfg['nit'] ?? null) ? $cfg['nit'] : '',
                signUser: is_string($credentials['sign_user'] ?? null) ? $credentials['sign_user'] : '',
                signKey: is_string($credentials['sign_key'] ?? null) ? $credentials['sign_key'] : '',
                apiUser: is_string($credentials['api_user'] ?? null) ? $credentials['api_user'] : '',
                apiKey: is_string($credentials['api_key'] ?? null) ? $credentials['api_key'] : '',
                environment: Environment::from(is_string($cfg['environment'] ?? null) ? $cfg['environment'] : 'sandbox'),
                flow: Flow::from(is_string($cfg['flow'] ?? null) ? $cfg['flow'] : 'unified'),
                retryTimes: (int) (is_numeric($retry['times'] ?? null) ? $retry['times'] : 3),
                retrySleep: (int) (is_numeric($retry['sleep'] ?? null) ? $retry['sleep'] : 2),
                fallbackEnabled: (bool) ($fallback['enabled'] ?? true),
                endpointSign: is_string($endpoints['sign'] ?? null) ? $endpoints['sign'] : '',
                endpointCertify: is_string($endpoints['certify'] ?? null) ? $endpoints['certify'] : '',
                endpointCancel: is_string($endpoints['cancel'] ?? null) ? $endpoints['cancel'] : '',
                endpointUnified: is_string($endpoints['unified'] ?? null) ? $endpoints['unified'] : '',
                endpointNit: is_string($endpoints['nit'] ?? null) ? $endpoints['nit'] : '',
                endpointCui: is_string($endpoints['cui'] ?? null) ? $endpoints['cui'] : '',
                endpointCuiAuth: is_string($endpoints['cui_auth'] ?? null) ? $endpoints['cui_auth'] : '',
            );
        });

        $this->app->singleton(ClientInterface::class, function (): ClientInterface {
            return new \GuzzleHttp\Client([
                'connect_timeout' => 5,
                'timeout'         => 30,
            ]);
        });

        $this->app->singleton(RequestFactoryInterface::class, function (): RequestFactoryInterface {
            return new \GuzzleHttp\Psr7\HttpFactory();
        });

        $this->app->singleton(StreamFactoryInterface::class, function (): StreamFactoryInterface {
            return new \GuzzleHttp\Psr7\HttpFactory();
        });

        $this->app->singleton(InfileClient::class, function (): InfileClient {
            return new InfileClient(
                $this->app->make(FelConfig::class),
                $this->app->make(ClientInterface::class),
                $this->app->make(RequestFactoryInterface::class),
                $this->app->make(StreamFactoryInterface::class),
            );
        });

        // Register FEL Studio only in local or testing environments
        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;
        if ($app->isLocal() || $app->runningUnitTests()) {
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

        InfilePhp::configure(
            $config,
            $this->app->make(ClientInterface::class),
            $this->app->make(RequestFactoryInterface::class),
            $this->app->make(StreamFactoryInterface::class),
            $laravelDispatcher,
        );

        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;

        if ($app->runningInConsole()) {
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
