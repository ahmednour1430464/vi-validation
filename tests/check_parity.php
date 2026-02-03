<?php

require __DIR__ . '/../vendor/autoload.php';

use Vi\Validation\Laravel\FastValidatorFactory;
use Vi\Validation\Rules\RuleRegistry;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Illuminate\Container\Container;

// Setup
$app = new Container();
$app->singleton(FastValidatorFactory::class, function () {
    $registry = new RuleRegistry();
    $registry->registerBuiltInRules();
    return new FastValidatorFactory([
        'compilation' => [
            'cache_path' => __DIR__ . '/../storage/framework/validation/compiled'
        ]
    ], $registry);
});

// Register Laravel Validator
$app->singleton('validator', function ($app) {
    return new \Illuminate\Validation\Factory(
        new \Illuminate\Translation\Translator(
            new \Illuminate\Translation\ArrayLoader(), 'en'
        ),
        $app
    );
});

Container::setInstance($app);
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);

$rules = [
    "name" => ["required", "string", "alpha_dash", "max:100"],
    "email" => ["required", "email"],
    "age" => ["required", "integer", "min:18", "max:28"]
];

$piece = [
  ["name" => "John Doe", "email" => "john@example.com", "age" => 25],
  ["name" => "Sarah Miller", "email" => "sarah.miller@example.com", "age" => 32],
  ["name" => "Michael Chen", "email" => "m.chen@example.com", "age" => 28],
  ["name" => "admin", "email" => "invalid-email", "age" => 15],
  ["name" => "Ok", "email" => "", "age" => 20],
  ["email" => "x@y.com", "age" => 17]
];

echo "Testing Parity...\n\n";

foreach ($piece as $i => $data) {
    echo "Item $i: " . json_encode($data) . "\n";
    
    // Laravel
    $lv = \Illuminate\Support\Facades\Validator::make($data, $rules);
    $lvValid = $lv->passes();
    $lvErrors = $lv->errors()->toArray();
    
    // Fast
    $fv = \Vi\Validation\Laravel\Facades\FastValidator::make($data, $rules);
    $fvValid = $fv->passes();
    $fvErrors = $fv->errors();
    
    echo "  Laravel: " . ($lvValid ? "VALID" : "INVALID") . ( $lvValid ? "" : " (" . json_encode($lvErrors) . ")" ) . "\n";
    echo "  Fast:    " . ($fvValid ? "VALID" : "INVALID") . ( $fvValid ? "" : " (" . json_encode($fvErrors) . ")" ) . "\n";
    
    if ($lvValid !== $fvValid) {
        echo "  !!! MISMATCH !!!\n";
    }
    echo "\n";
}
