<?php

require __DIR__ . '/vendor/autoload.php';

use Vi\Validation\Laravel\FastValidatorFactory;

echo "Testing FastValidator with new rules...\n\n";

// Create factory instance
$factory = new FastValidatorFactory([
    'cache' => ['enabled' => false],
    'localization' => ['locale' => 'en']
]);

// Test 1: alpha_dash rule
echo "Test 1: alpha_dash rule\n";
echo "------------------------\n";

$inputs1 = [
    "name" => "test-name_123",
    "email" => "test@example.com",
    "age" => 25
];

$validator1 = $factory->make($inputs1, [
    "name" => ["required", "string", "alpha_dash", "max:100"],
    "email" => ["required", "email"],
    "age" => ["required", "integer", "min:18"]
]);

if ($validator1->passes()) {
    echo "✓ Test 1 PASSED: alpha_dash validation works!\n";
} else {
    echo "✗ Test 1 FAILED:\n";
    print_r($validator1->errors()->toArray());
}

// Test 2: alpha_dash rule with invalid input
echo "\nTest 2: alpha_dash rule with invalid input (should fail)\n";
echo "--------------------------------------------------------\n";

$inputs2 = [
    "name" => "test name@123", // Contains space and @, should fail
];

$validator2 = $factory->make($inputs2, [
    "name" => ["required", "alpha_dash"],
]);

if ($validator2->fails()) {
    echo "✓ Test 2 PASSED: alpha_dash correctly rejects invalid input\n";
    echo "  Errors: " . implode(', ', $validator2->errors()->get('name')) . "\n";
} else {
    echo "✗ Test 2 FAILED: Should have rejected input with space and @\n";
}

// Test 3: lowercase rule
echo "\nTest 3: lowercase rule\n";
echo "----------------------\n";

$inputs3 = ["username" => "lowercase"];
$validator3 = $factory->make($inputs3, ["username" => ["lowercase"]]);

if ($validator3->passes()) {
    echo "✓ Test 3 PASSED: lowercase validation works!\n";
} else {
    echo "✗ Test 3 FAILED:\n";
    print_r($validator3->errors()->toArray());
}

// Test 4: uppercase rule
echo "\nTest 4: uppercase rule\n";
echo "----------------------\n";

$inputs4 = ["code" => "UPPERCASE"];
$validator4 = $factory->make($inputs4, ["code" => ["uppercase"]]);

if ($validator4->passes()) {
    echo "✓ Test 4 PASSED: uppercase validation works!\n";
} else {
    echo "✗ Test 4 FAILED:\n";
    print_r($validator4->errors()->toArray());
}

// Test 5: multiple_of rule
echo "\nTest 5: multiple_of rule\n";
echo "------------------------\n";

$inputs5 = ["quantity" => 15];
$validator5 = $factory->make($inputs5, ["quantity" => ["multiple_of:5"]]);

if ($validator5->passes()) {
    echo "✓ Test 5 PASSED: multiple_of validation works!\n";
} else {
    echo "✗ Test 5 FAILED:\n";
    print_r($validator5->errors()->toArray());
}

// Test 6: doesnt_start_with rule
echo "\nTest 6: doesnt_start_with rule\n";
echo "------------------------------\n";

$inputs6 = ["username" => "user123"];
$validator6 = $factory->make($inputs6, ["username" => ["doesnt_start_with:admin,root"]]);

if ($validator6->passes()) {
    echo "✓ Test 6 PASSED: doesnt_start_with validation works!\n";
} else {
    echo "✗ Test 6 FAILED:\n";
    print_r($validator6->errors()->toArray());
}

echo "\n======================\n";
echo "All tests completed!\n";
echo "======================\n";
