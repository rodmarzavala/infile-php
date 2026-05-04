<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InfilePhp\Laravel\Studio\Http\Controllers\Api\TimelineController;
use InfilePhp\Laravel\Studio\Http\Controllers\StudioController;
use InfilePhp\Laravel\Studio\Listeners\StudioEventSubscriber;
use InfilePhp\Laravel\Studio\Storage\StudioRepository;

final class StudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;

        // Boot handles routes and everything else

        $this->app->singleton(StudioRepository::class);
    }

    public function boot(): void
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;

        // Check access middleware handles authorization for the routes

        // Register the event subscriber to capture DTE events
        $this->app->make(Dispatcher::class)->subscribe(StudioEventSubscriber::class);

        // Register Studio Routes
        Route::middleware(['web', \InfilePhp\Laravel\Studio\Http\Middleware\Authorize::class])
            ->prefix('fel-studio')
            ->group(function () {
                // API Routes
                Route::prefix('api')
                    ->group(function () {
                        Route::get('timeline', [TimelineController::class, 'index']);
                        Route::get('health', [\InfilePhp\Laravel\Studio\Http\Controllers\Api\HealthController::class, 'index']);
                        Route::post('builder/preview', [\InfilePhp\Laravel\Studio\Http\Controllers\Api\BuilderController::class, 'preview']);
                        Route::post('builder/validate', [\InfilePhp\Laravel\Studio\Http\Controllers\Api\BuilderController::class, 'validate']);
                    });

                // Catch-all route for the SPA
                Route::get('/{view?}', [StudioController::class, 'index'])
                    ->where('view', '(.*)')
                    ->name('fel-studio.index');
            });

        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;

        if (config('felkit.studio.driver') === 'database') {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }

        // Publish UI assets from the agnostic frontend
        if ($app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../resources/studio-ui' => public_path('vendor/fel-studio'),
            ], 'fel-studio-assets');
        }
    }
}
