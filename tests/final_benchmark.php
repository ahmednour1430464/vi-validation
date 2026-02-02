<?php

require __DIR__ . '/../vendor/autoload.php';

use Vi\Validation\SchemaValidator;
use Vi\Validation\Schema\SchemaBuilder;

// Ensure we have a cache directory
$cachePath = __DIR__ . '/../storage/framework/validation/compiled';
if (!is_dir($cachePath)) {
    mkdir($cachePath, 0755, true);
}

echo "--- Speed & Parity Benchmark ---\n\n";

$rules = function (SchemaBuilder $builder) {
    $builder->field('name')->required()->string()->min(3)->max(50);
    $builder->field('email')->required()->email();
    $builder->field('age')->integer()->min(18)->nullable();
    $builder->field('bio')->sometimes()->string()->max(1000);
    $builder->field('settings.theme')->string();
};

// 1. Force compilation
echo "Compiling schema...\n";
$validator = SchemaValidator::build($rules, [
    'compilation' => ['cache_path' => $cachePath]
]);

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25,
    'bio' => 'Software Engineer',
    'settings' => ['theme' => 'dark']
];

// 2. Parity check
echo "Checking results parity...\n";
$result = $validator->validate($data);

if (!$result->isValid()) {
    echo "✗ Validation FAILED when it should have PASSED!\n";
    print_r($result->errors());
    exit(1);
}
echo "✓ Parity Check PASSED (Normal data)\n";

$invalidData = [
    'name' => 'Jo', // too short
    'email' => 'invalid-email',
    'age' => 15, // too young
];

$resultInvalid = $validator->validate($invalidData);
if ($resultInvalid->isValid()) {
    echo "✗ Validation PASSED when it should have FAILED!\n";
    exit(1);
}
echo "✓ Parity Check PASSED (Invalid data)\n";

// 3. Performance Benchmark
echo "\nRunning 50k iterations...\n";

$count = 50000;
$start = hrtime(true);
for ($i = 0; $i < $count; $i++) {
    $validator->validate($data);
}
$end = hrtime(true);

$duration = ($end - $start) / 1e9; // convert to seconds
$throughput = $count / $duration;

echo "Total Time: " . number_format($duration, 4) . "s\n";
echo "Throughput: " . number_format($throughput, 0) . " validations/sec\n";
echo "Average Time: " . number_format(($duration / $count) * 1e6, 3) . " μs/validation\n";

echo "\n--- Benchmark Complete ---\n";
