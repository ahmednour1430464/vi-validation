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

function generateRandomItem() {
    $names = ["John Doe", "Sarah_Miller", "Michael-Chen", "admin", "Ok", "Ahmed", "Multi Byte \u{0627}\u{0644}\u{0639}\u{0631}\u{0628}\u{064A}\u{0629}"];
    $emails = ["john@example.com", "sarah.miller@example.com", "m.chen@example.com", "invalid-email", "", null, "x@y.com"];
    $ages = [25, 32, 28, 15, 20, 17, "25", "15", null];
    
    $item = [];
    if (rand(0, 5) > 0) $item['name'] = $names[array_rand($names)];
    if (rand(0, 5) > 0) $item['email'] = $emails[array_rand($emails)];
    if (rand(0, 5) > 0) $item['age'] = $ages[array_rand($ages)];
    
    return $item;
}

echo "Fuzzing for discrepancies...\n";

$found = 0;
for ($i = 0; $i < 1000; $i++) {
    $data = generateRandomItem();
    
    $lv = \Illuminate\Support\Facades\Validator::make($data, $rules);
    $fv = \Vi\Validation\Laravel\Facades\FastValidator::make($data, $rules);
    
    if ($lv->passes() !== $fv->passes()) {
        $found++;
        echo "\nMISMATCH FOUND!\n";
        echo "Data: " . json_encode($data) . "\n";
        echo "Laravel: " . ($lv->passes() ? "PASS" : "FAIL") . " " . json_encode($lv->errors()->toArray()) . "\n";
        echo "Fast:    " . ($fv->passes() ? "PASS" : "FAIL") . " " . json_encode($fv->errors()->toArray()) . "\n";
        if ($found >= 5) break;
    }
}

if ($found === 0) {
    echo "No mismatches found in 1000 random items.\n";
}
