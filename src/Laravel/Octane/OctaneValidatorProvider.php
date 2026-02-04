<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel\Octane;

use Illuminate\Support\ServiceProvider;
// Octane classes imported but will be checked for existence
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
        if (class_exists('Laravel\Octane\Events\WorkerStarting')) {
            $events->listen('Laravel\Octane\Events\WorkerStarting', function () {
                $this->app->make(ValidatorPool::class)->onWorkerStart();
                $this->app->make(ContextManager::class)->onWorkerStart();
            });
        }

        if (class_exists('Laravel\Octane\Events\WorkerStopping')) {
            $events->listen('Laravel\Octane\Events\WorkerStopping', function () {
                $this->app->make(ValidatorPool::class)->onWorkerStop();
                $this->app->make(ContextManager::class)->onWorkerStop();
            });
        }

        // Request lifecycle events
        if (class_exists('Laravel\Octane\Events\RequestReceived')) {
            $events->listen('Laravel\Octane\Events\RequestReceived', function () {
                $this->app->make(ContextManager::class)->onRequestStart();
            });
        }

        if (class_exists('Laravel\Octane\Events\RequestTerminated')) {
            $events->listen('Laravel\Octane\Events\RequestTerminated', function () {
                $this->app->make(ContextManager::class)->onRequestEnd();
            });
        }
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
