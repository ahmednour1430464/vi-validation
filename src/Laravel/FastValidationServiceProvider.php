<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Illuminate\Support\Facades\Validator as LaravelValidator;
use Illuminate\Support\ServiceProvider;

final class FastValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FastValidatorFactory::class, function ($app) {
            return new FastValidatorFactory(config('fast-validation'));
        });

        $this->app->alias(FastValidatorFactory::class, 'fast.validator');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/fast-validation.php' => config_path('fast-validation.php'),
        ], 'config');

        $mode = config('fast-validation.mode', 'parallel');

        if ($mode === 'override') {
            $this->overrideLaravelValidator();
        }
    }

    private function overrideLaravelValidator(): void
    {
        $this->app->extend('validator', function ($validator, $app) {
            $factory = $app->make(FastValidatorFactory::class);

            return new LaravelValidatorAdapter($factory, $validator);
        });
    }
}
