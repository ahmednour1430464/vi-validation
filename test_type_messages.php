<?php

require __DIR__ . '/vendor/autoload.php';

use Vi\Validation\Laravel\FastValidatorFactory;

echo "Testing type-specific validation messages...\n\n";

// Create factory instance
$factory = new FastValidatorFactory([
    'cache' => ['enabled' => false],
    'localization' => ['locale' => 'en']
]);

// Test 1: min rule with numeric value
echo "Test 1: min rule with numeric value\n";
echo "------------------------------------\n";
$validator1 = $factory->make(['age' => 10], ['age' => ['min:18']]);
if ($validator1->fails()) {
    echo "✓ Error message: " . $validator1->errors()->first('age') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 2: min rule with string value
echo "\nTest 2: min rule with string value\n";
echo "-----------------------------------\n";
$validator2 = $factory->make(['name' => 'ab'], ['name' => ['min:5']]);
if ($validator2->fails()) {
    echo "✓ Error message: " . $validator2->errors()->first('name') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 3: min rule with array value
echo "\nTest 3: min rule with array value\n";
echo "----------------------------------\n";
$validator3 = $factory->make(['items' => [1]], ['items' => ['min:3']]);
if ($validator3->fails()) {
    echo "✓ Error message: " . $validator3->errors()->first('items') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 4: max rule with numeric value
echo "\nTest 4: max rule with numeric value\n";
echo "------------------------------------\n";
$validator4 = $factory->make(['score' => 150], ['score' => ['max:100']]);
if ($validator4->fails()) {
    echo "✓ Error message: " . $validator4->errors()->first('score') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 5: max rule with string value
echo "\nTest 5: max rule with string value\n";
echo "-----------------------------------\n";
$validator5 = $factory->make(['title' => 'This is a very long title'], ['title' => ['max:10']]);
if ($validator5->fails()) {
    echo "✓ Error message: " . $validator5->errors()->first('title') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 6: size rule with numeric value
echo "\nTest 6: size rule with numeric value\n";
echo "-------------------------------------\n";
$validator6 = $factory->make(['quantity' => 5], ['quantity' => ['size:10']]);
if ($validator6->fails()) {
    echo "✓ Error message: " . $validator6->errors()->first('quantity') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 7: between rule with numeric value
echo "\nTest 7: between rule with numeric value\n";
echo "----------------------------------------\n";
$validator7 = $factory->make(['age' => 5], ['age' => ['between:18,65']]);
if ($validator7->fails()) {
    echo "✓ Error message: " . $validator7->errors()->first('age') . "\n";
} else {
    echo "✗ Should have failed\n";
}

// Test 8: Original user test case
echo "\nTest 8: Original user test case\n";
echo "--------------------------------\n";
$inputs = [
    "name" => "test-name_123",
    "email" => "test@example.com",
    "age" => 10  // Should fail min:18
];

$validator8 = $factory->make($inputs, [
    "name" => ["required", "string", "alpha_dash", "max:100"],
    "email" => ["required", "email"],
    "age" => ["required", "integer", "min:18"]
]);

if ($validator8->fails()) {
    echo "✓ Validation failed as expected\n";
    echo "Errors:\n";
    foreach ($validator8->errors()->toArray() as $field => $messages) {
        foreach ($messages as $message) {
            echo "  - $field: $message\n";
        }
    }
} else {
    echo "✗ Should have failed\n";
}

echo "\n======================\n";
echo "All tests completed!\n";
echo "======================\n";
