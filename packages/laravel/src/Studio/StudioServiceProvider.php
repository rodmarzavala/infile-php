<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use InfilePhp\Laravel\Studio\Http\Controllers\StudioController;
use InfilePhp\Laravel\Studio\Http\Controllers\Api\TimelineController;
use InfilePhp\Laravel\Studio\Listeners\StudioEventSubscriber;
use InfilePhp\Laravel\Studio\Storage\StudioRepository;
use Illuminate\Events\Dispatcher;

final class StudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!$this->app->isLocal() && !$this->app->runningUnitTests()) {
            return;
        }

        $this->app->singleton(StudioRepository::class);
    }

    public function boot(): void
    {
        if (!$this->app->isLocal() && !$this->app->runningUnitTests()) {
            return;
        }

        // Register the event subscriber to capture DTE events
        $this->app->make(Dispatcher::class)->subscribe(StudioEventSubscriber::class);

        // Register Studio Routes
        Route::middleware(['web'])
            ->prefix('fel-studio')
            ->group(function () {
                // API Routes
                Route::prefix('api')->group(function () {
                    Route::get('timeline', [TimelineController::class, 'index']);
                    Route::post('builder/preview', [\InfilePhp\Laravel\Studio\Http\Controllers\Api\BuilderController::class, 'preview']);
                    Route::post('builder/validate', [\InfilePhp\Laravel\Studio\Http\Controllers\Api\BuilderController::class, 'validate']);
                });

                // Catch-all route for the SPA
                Route::get('/{view?}', [StudioController::class, 'index'])
                    ->where('view', '(.*)')
                    ->name('fel-studio.index');
            });

        // Publish UI assets from the agnostic frontend
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../resources/studio-ui' => public_path('vendor/fel-studio'),
            ], 'fel-studio-assets');
        }
    }
}
