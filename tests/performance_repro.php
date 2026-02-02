            <?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Vi\Validation\Laravel\Facades\FastValidator;
use Vi\Validation\Laravel\FastValidatorFactory;
use Vi\Validation\Schema\SchemaBuilder;

if (!function_exists('dd')) {
    function dd(...$args) {
        foreach ($args as $arg) {
            print_r($arg);
        }
        exit(1);
    }
}
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

$piece = [
  ["name" => "John Doe", "email" => "john@example.com", "age" => 25],
  [
    "name" => "Sarah Miller",
    "email" => "sarah.miller@example.com",
    "age" => 32
  ],
  ["name" => "Michael Chen", "email" => "m.chen@example.com", "age" => 28],
  ["name" => "admin", "email" => "invalid-email", "age" => 15], // all invalid
  ["name" => "Ok", "email" => "", "age" => 20], // email missing
  ["email" => "x@y.com", "age" => 17] // name missing
];

$datastream = function (array $piece, int $count): Generator {
  $max = count($piece) - 1;

  for ($i = 0; $i < $count; $i++) {
    yield $piece[random_int(0, $max)];
  }
};

function testViValidationStream($inputs): int
{
  $validator = FastValidator::make($inputs, [
    "name" => [
      "required",
      "string",
      "alpha_dash",
      "max:100"
    ],
    "email" => ["required", "email"],
    "age" => ["required", "integer", "min:18", "max:28"]
  ]);

  $validCount = 0;

  foreach ($validator->stream() as $index => $result) {
    if ($result->isValid()) {
      $validCount++;
    }
  }

  return $validCount;
}

$count = 100_000;
$inputs = iterator_to_array($datastream($piece, $count)); // Materialize to avoid generator overhead in benchmark comparison if needed, but the user used generator

// Run once to warm up cache/compilation
testViValidationStream($inputs);

Benchmark::dd(
  [
    "FastValidator" => fn() => testViValidationStream($inputs)
  ],
  iterations: 1
);
