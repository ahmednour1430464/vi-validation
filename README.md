## vi/validation – High-Performance PHP Validator

**vi/validation** is a high-performance PHP validation library optimized for large datasets, with first-class Laravel integration.
It focuses on **compile-once, validate-many** semantics and efficient handling of nested data (up to depth 2).

---

## Requirements

- **PHP**: >= 8.1
- **Composer**
- **Laravel integration (optional)**: `illuminate/validation` ^10.0 | ^11.0

---

## Installation

Install via Composer:

```bash
composer require vi/validation
```

This registers PSR-4 autoloading for:

- `Vi\\Validation\\` → `src/`

---

## Quick Start

### Standalone (PHP)

```php
use Vi\\Validation\\Validator;
use Vi\\Validation\\SchemaValidator;

$schema = Validator::schema()
    ->field('email')->required()->email()->end()
    ->compile();

$validator = new SchemaValidator($schema);

$result = $validator->validate([
    'email' => 'user@example.com',
]);

if (!$result->isValid()) {
    dd($result->errors());
}
```

### Laravel (Parallel Mode)

```php
use Vi\\Validation\\Laravel\\FastValidatorFactory;

public function store()
{
    /** @var FastValidatorFactory $factory */
    $factory = app(FastValidatorFactory::class);

    $validator = $factory->make(request()->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // ...
}
```

---

## Core Concepts

- **Schema**: A reusable, compiled description of your validation rules.
- **ValidatorEngine**: Executes validations with minimal overhead.
- **ValidationResult**: Encapsulates validation outcome and errors.
- **Nested fields**: Supported up to **2 levels** using dot notation (e.g., `user.email`).

---

## Standalone PHP Usage

### Define a Schema

Use the `Validator` facade to build schemas:

```php
use Vi\\Validation\\Validator;
use Vi\\Validation\\SchemaValidator;

$schema = Validator::schema()
    ->field('name')->required()->string()->max(100)->end()
    ->field('email')->required()->email()->end()
    ->field('age')->required()->integer()->min(18)->end()
    ->compile();

$validator = new SchemaValidator($schema);
```

### Validate a Single Record

```php
$data = [
    'name'  => 'John Doe',
    'email' => 'john@example.com',
    'age'   => 25,
];

$result = $validator->validate($data);

if ($result->isValid()) {
    // OK
} else {
    $errors = $result->errors();
    // $errors is: array<string, list<array{rule: string, message?: string|null}>>
}
```

### Nested Fields (Max Depth = 2)

Nested structures up to **two levels** are supported via dot notation:

```php
$schema = Validator::schema()
    ->field('user.name')->required()->string()->end()
    ->field('user.email')->required()->email()->end()
    ->compile();

$validator = new SchemaValidator($schema);

$data = [
    'user' => [
        'name'  => 'Jane Doe',
        'email' => 'jane@example.com',
    ],
];

$result = $validator->validate($data);
```

Fields such as `user.name` and `user.email` map to `$data['user']['name']` and `$data['user']['email']`.

### Nullable Fields

Use `nullable()` to allow `null` values while still enforcing other rules when present:

```php
$schema = Validator::schema()
    ->field('optional_field')->nullable()->string()->end()
    ->compile();

$validator = new SchemaValidator($schema);

$result = $validator->validate([
    'optional_field' => null,
]);

// Passes: field is nullable and value is null
```

The engine will **short-circuit** other rules if the value is `null` and the field has `nullable()`.

### Batch Validation (validateMany)

Reuse the same schema across many records:

```php
use Vi\\Validation\\SchemaValidator;
use Vi\\Validation\\Validator;

$schema = Validator::schema()
    ->field('id')->required()->integer()->end()
    ->field('name')->required()->string()->end()
    ->compile();

$validator = new SchemaValidator($schema);

$rows = [
    ['id' => 1, 'name' => 'Alice'],
    ['id' => 2, 'name' => 'Bob'],
    ['id' => 3, 'name' => 'Charlie'],
];

$results = $validator->validateMany($rows); // array of ValidationResult

foreach ($results as $index => $result) {
    if (!$result->isValid()) {
        $errors = $result->errors();
        // Handle errors for $rows[$index]
    }
}
```

### Chunked Validation for Large Datasets

For very large datasets or streams, use chunked validation:

```php
use Vi\\Validation\\Validator;
use Vi\\Validation\\SchemaValidator;
use Vi\\Validation\\Execution\\ChunkedValidator;

$schema = Validator::schema()
    ->field('id')->required()->integer()->end()
    ->field('name')->required()->string()->end()
    ->compile();

$schemaValidator = new SchemaValidator($schema);
$chunked = new ChunkedValidator($schemaValidator);

/** @var iterable<array<string, mixed>> $rows */
$rows = /* stream / generator / large array */;

$chunked->validateInChunks($rows, 1000, function (int $chunkIndex, array $results): void {
    foreach ($results as $rowIndex => $result) {
        if (!$result->isValid()) {
            $errors = $result->errors();
            // Handle errors for this row in this chunk
        }
    }
});
```

---

## Laravel Integration

### Register the Service Provider

If auto-discovery is not used, add the service provider to `config/app.php`:

```php
'providers' => [
    // ...
    Vi\\Validation\\Laravel\\FastValidationServiceProvider::class,
],
```

The provider:

- Registers a singleton `FastValidatorFactory`.
- Aliases it as `fast.validator` in the container.
- Publishes the configuration file.
- Optionally overrides Laravel's validator depending on the mode.

### Publish Configuration

Publish the `fast-validation.php` config file:

```bash
php artisan vendor:publish \
    --provider="Vi\\Validation\\Laravel\\FastValidationServiceProvider" \
    --tag=config
```

This creates `config/fast-validation.php`:

```php
<?php

return [
    // 'parallel' => use FastValidator as a separate, opt-in API.
    // 'override' => route Laravel's Validator::make() through the fast engine when possible.
    'mode' => 'parallel',

    // Future toggles: error detail level, caching, etc.
];
```

### Modes: Parallel vs Override

- **parallel** (default)
  - You call the fast validator explicitly via the container.
  - Laravel's built-in `Validator::make()` continues to work as usual.

- **override**
  - The package wraps Laravel's validator factory.
  - Calls to `Validator::make()` are routed through the fast engine (for supported rules).

Set the mode in `config/fast-validation.php`:

```php
return [
    'mode' => 'parallel', // or 'override'
];
```

### Using the Fast Validator in Parallel Mode

In **parallel mode**, use the factory directly:

```php
use Vi\\Validation\\Laravel\\FastValidatorFactory;

public function store()
{
    /** @var FastValidatorFactory $factory */
    $factory = app(FastValidatorFactory::class);
    // or: $factory = app('fast.validator');

    $data = request()->all();

    $validator = $factory->make($data, [
        'name'  => 'required|string|max:100',
        'email' => 'required|email',
        'age'   => 'required|integer|min:18',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // ... proceed with storing the model
}
```

`FastValidatorFactory::make()` returns an implementation of `Illuminate\\Contracts\\Validation\\Validator`, so you can use:

- `$validator->fails()`
- `$validator->errors()`
- `$validator->validated()`
- `$validator->getData()`

### Using Override Mode (Drop-in Replacement)

In **override mode** (`'mode' => 'override'`), your existing Laravel validation code continues to work, but is backed by the fast engine for supported rules:

```php
use Illuminate\\Support\\Facades\\Validator;

$validator = Validator::make($data, [
    'name'  => 'required|string|max:100',
    'email' => 'required|email',
    'age'   => 'required|integer|min:18',
]);

if ($validator->fails()) {
    return back()->withErrors($validator)->withInput();
}
```

> **Note:** At this stage, only a subset of Laravel rules is supported in the fast path. Unsupported rules are currently ignored by the parser.
> For critical validations, start with **parallel mode** and migrate carefully.

---

## Supported Rules (Initial Set)

The Laravel-style rules currently supported and mapped internally are:

- **required** → checks for non-null, non-empty string, non-empty array
- **string** → value must be a string
- **integer** → value must be an integer
- **email** → validates using `filter_var(…, FILTER_VALIDATE_EMAIL)`
- **min:x** →
  - For strings/arrays: minimum length/size
  - For numeric values: minimum numeric value
- **max:x** →
  - For strings/arrays: maximum length/size
  - For numeric values: maximum numeric value

Example:

```php
$rules = [
    'name'  => 'required|string|max:100',
    'email' => 'required|email',
    'age'   => 'required|integer|min:18',
];
```

---

## Testing

If you have the repo cloned locally, you can run the test suite with:

```bash
composer install

./vendor/bin/phpunit
```

This runs unit tests defined under `tests/`, including basic validation, nested fields, nullable fields, and batch validation scenarios.

---

## Roadmap (High Level)

Planned future enhancements include:

- Additional built-in rules and richer Laravel rule mapping.
- More advanced compilation strategies and micro-optimizations.
- More detailed error messages and localization support.
- Deeper integration options for long-running processes (Octane, Swoole, RoadRunner).
