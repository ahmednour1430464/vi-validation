<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel\Octane;

use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Vi\Validation\Runtime\ValidatorPool;
use Vi\Validation\Runtime\ContextManager;

/**
 * Service provider for Laravel Octane integration.
 */
class OctaneValidatorProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ValidatorPool::class, function ($app) {
            $config = $app['config']->get('fast-validation.runtime', []);
            $poolSize = $config['pool_size'] ?? 10;
            
            return new ValidatorPool($poolSize);
        });

        $this->app->singleton(ContextManager::class, function () {
            return new ContextManager();
        });
    }

    public function boot(): void
    {
        if (!$this->isOctaneEnvironment()) {
            return;
        }

        $this->registerOctaneListeners();
    }

    private function registerOctaneListeners(): void
    {
        $events = $this->app['events'];

        // Worker lifecycle events
        $events->listen(WorkerStarting::class, function () {
            $this->app->make(ValidatorPool::class)->onWorkerStart();
            $this->app->make(ContextManager::class)->onWorkerStart();
        });

        $events->listen(WorkerStopping::class, function () {
            $this->app->make(ValidatorPool::class)->onWorkerStop();
            $this->app->make(ContextManager::class)->onWorkerStop();
        });

        // Request lifecycle events
        $events->listen(RequestReceived::class, function () {
            $this->app->make(ContextManager::class)->onRequestStart();
        });

        $events->listen(RequestTerminated::class, function () {
            $this->app->make(ContextManager::class)->onRequestEnd();
        });
    }

    private function isOctaneEnvironment(): bool
    {
        return class_exists('Laravel\Octane\Octane') ||
               isset($_SERVER['LARAVEL_OCTANE']) ||
               getenv('LARAVEL_OCTANE') !== false;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            ValidatorPool::class,
            ContextManager::class,
        ];
    }
}
