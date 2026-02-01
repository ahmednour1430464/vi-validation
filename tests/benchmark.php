<?php

require __DIR__ . '/../vendor/autoload.php';

use Vi\Validation\SchemaValidator;
use Vi\Validation\Schema\SchemaBuilder;

// Disable garbage collection to prevent GC spikes from affecting the benchmark
gc_collect_cycles();
gc_disable();

echo "Generating data...\n";
$start = microtime(true);

$validator = SchemaValidator::build(function (SchemaBuilder $builder) {
    $builder->field('name')->string()->min(3)->max(50);
    $builder->field('email')->email();
    $builder->field('age')->integer()->min(18)->nullable();
    $builder->field('address.city')->string();
    $builder->field('address.zip')->string()->regex('/^\d{5}$/');
});

$data = [];
for ($i = 0; $i < 10000; $i++) {
    $data[] = [
        'name' => 'User ' . $i,
        'email' => 'user' . $i . '@example.com',
        'age' => $i % 3 === 0 ? null : 20 + ($i % 50),
        'address' => [
            'city' => 'City ' . $i,
            'zip' => '12345',
        ],
    ];
}

$prepTime = microtime(true) - $start;
echo "Preparation time: " . number_format($prepTime, 4) . "s\n";

echo "Running 10k validations...\n";
$start = microtime(true);
$results = $validator->validateMany($data);
$duration = microtime(true) - $start;

echo "Validation time: " . number_format($duration, 4) . "s\n";
echo "Throughput: " . number_format(10000 / $duration, 2) . " rows/s\n";

// Count errors to make sure validation actually happened
$failureCount = 0;
foreach ($results as $result) {
    if (!$result->isValid()) {
        $failureCount++;
    }
}
echo "Failures: $failureCount\n";
