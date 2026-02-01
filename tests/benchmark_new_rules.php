<?php

require __DIR__ . '/../vendor/autoload.php';

use Vi\Validation\SchemaValidator;
use Vi\Validation\Schema\SchemaBuilder;
use Vi\Validation\Rules\ActiveUrlRule;
use Vi\Validation\Rules\AlphaDashRule;
use Vi\Validation\Rules\MacAddressRule;
use Vi\Validation\Rules\UlidRule;
use Vi\Validation\Rules\UppercaseRule;
use Vi\Validation\Rules\DateFormatRule;
use Vi\Validation\Rules\MultipleOfRule;
use Vi\Validation\Rules\NotRegexRule;

// Disable garbage collection
gc_collect_cycles();
gc_disable();

echo "Generating data for new rules benchmark...\n";
$start = microtime(true);

$validator = SchemaValidator::build(function (SchemaBuilder $builder) {
    // $builder->field('url')->rules(new ActiveUrlRule());
    $builder->field('username')->rules(new AlphaDashRule());
    $builder->field('mac')->rules(new MacAddressRule());
    $builder->field('id')->rules(new UlidRule());
    $builder->field('code')->rules(new UppercaseRule());
    $builder->field('birthdate')->rules(new DateFormatRule('Y-m-d'));
    $builder->field('score')->rules(new MultipleOfRule(5));
    $builder->field('comment')->rules(new NotRegexRule('/badword/'));
});

$data = [];
$count = 10000;
for ($i = 0; $i < $count; $i++) {
    $data[] = [
        'url' => 'https://example.com',
        'username' => 'user_name-123',
        'mac' => '00:0a:95:9d:68:16',
        'id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
        'code' => 'CODE',
        'birthdate' => '2023-01-01',
        'score' => 10,
        'comment' => 'This is a good comment',
    ];
}

$prepTime = microtime(true) - $start;
echo "Preparation time: " . number_format($prepTime, 4) . "s\n";

echo "Running $count validations...\n";
$start = microtime(true);
$results = $validator->validateMany($data);
$duration = microtime(true) - $start;

echo "Validation time: " . number_format($duration, 4) . "s\n";
echo "Throughput: " . number_format($count / $duration, 2) . " rows/s\n";

$failureCount = 0;
foreach ($results as $result) {
    if (!$result->isValid()) {
        $failureCount++;
    }
}
echo "Failures: $failureCount\n";
