<?php

require __DIR__ . '/../vendor/autoload.php';

use Vi\Validation\Laravel\Facades\FastValidator;
use Vi\Validation\Laravel\FastValidatorFactory;
use Vi\Validation\Rules\RuleRegistry;
use Illuminate\Container\Container;

// Setup a minimal Laravel-like environment for the facade
$app = new Container();
$app->singleton(FastValidatorFactory::class, function () {
    $registry = new RuleRegistry();
    $registry->registerBuiltInRules();
    return new FastValidatorFactory([
        'compilation' => [
            'cache_path' => __DIR__ . '/../storage/framework/validation/compiled'
        ],
        'performance' => [
            'fail_fast' => false
        ]
    ], $registry);
});
Container::setInstance($app);
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);

$data = [
    "name" => "a", // too short (min 3)
    "email" => "invalid",
    "age" => 15 // too young (min 18)
];

$rules = [
    "name" => "required|string|min:3|max:100",
    "email" => "required|email",
    "age" => "required|integer|min:18|max:28"
];

echo "Testing lazy message resolution...\n";

$validator = FastValidator::make($data, $rules);

if ($validator->passes()) {
    echo "FAIL: Expected validation to fail!\n";
    exit(1);
}

$errors = $validator->errors()->toArray();

echo "Messages:\n";
print_r($errors);

// Check if messages contains expected text (e.g., 'min' or 'email')
$foundMessage = false;
foreach ($errors as $field => $messages) {
    foreach ($messages as $message) {
        if (strpos($message, 'validation.') === 0) {
             echo "FAIL: Message was not resolved: $message\n";
             exit(1);
        }
        $foundMessage = true;
    }
}

if (!$foundMessage) {
    echo "FAIL: No messages found!\n";
    exit(1);
}

echo "SUCCESS: Messages are resolved correctly.\n";
