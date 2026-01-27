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

### Memory-Efficient Streaming Validation

For large datasets (10,000+ rows), avoid `validateMany()` as it materializes all results in memory. Instead, use the streaming APIs:

#### Generator-Based Streaming (Recommended)

The `stream()` method yields results one at a time, allowing PHP to garbage collect each result after processing:

```php
use Vi\Validation\SchemaValidator;
use Vi\Validation\Validator;

$schema = Validator::schema()
    ->field('id')->required()->integer()->end()
    ->field('email')->required()->email()->end()
    ->compile();

$validator = new SchemaValidator($schema);

// Stream 100,000 rows with O(1) memory usage
foreach ($validator->stream($rows) as $index => $result) {
    if (!$result->isValid()) {
        // Handle error - result is garbage collected after this iteration
        log_error("Row $index failed", $result->errors());
    }
}
```

#### Callback-Based Processing

The `each()` method processes results immediately without storing them:

```php
$validator->each($rows, function ($result, $index) {
    if (!$result->isValid()) {
        Log::error("Row $index failed", $result->errors());
    }
});
```

#### Stream Only Failures

For error reporting where you only care about failures:

```php
// Only yields failed validation results
foreach ($validator->failures($rows) as $index => $result) {
    echo "Row $index failed: " . json_encode($result->errors()) . "\n";
}
```

#### Fail-Fast Validation

Stop at the first failure:

```php
$firstError = $validator->firstFailure($rows);

if ($firstError !== null) {
    throw new ValidationException($firstError->errors());
}
```

#### Check All Valid

Memory-efficient way to check if all rows pass:

```php
if ($validator->allValid($rows)) {
    // All 100,000 rows passed validation
    $this->processImport($rows);
}
```

#### Chunked Streaming

For very large datasets where you need batch processing with controlled memory:

```php
use Vi\Validation\Execution\ChunkedValidator;

$chunked = new ChunkedValidator($validator);

// Stream chunks of BatchValidationResult
foreach ($chunked->streamChunks($rows, 1000) as $chunkIndex => $batchResult) {
    if (!$batchResult->allValid()) {
        foreach ($batchResult->failures() as $result) {
            // Handle failures in this chunk
        }
    }
}

// Or stream only failures with original row indices
foreach ($chunked->streamFailures($rows, 1000) as $originalIndex => $result) {
    echo "Row $originalIndex failed\n";
}

// Count failures without storing results
$failureCount = $chunked->countFailures($rows, 1000);
```

#### Memory Comparison

| Method | Memory Usage | Use Case |
|--------|--------------|----------|
| `validateMany()` | O(n) - stores all results | Small datasets (<10k rows) |
| `stream()` | O(1) - yields one at a time | Large datasets, ETL |
| `each()` | O(1) - callback, no storage | Fire-and-forget |
| `failures()` | O(1) - yields failures only | Error reporting |
| `streamChunks()` | O(chunk) - controlled batches | Batch inserts |

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

## Supported Rules

The Laravel-style rules currently supported and mapped internally are:

### Core Rules

| Rule | Description |
|------|-------------|
| `required` | Checks for non-null, non-empty string, non-empty array |
| `nullable` | Allows null values, skips other rules when null |

### Type Rules

| Rule | Description |
|------|-------------|
| `string` | Value must be a string |
| `integer` / `int` | Value must be an integer |
| `numeric` | Value must be numeric (int, float, or numeric string) |
| `boolean` / `bool` | Value must be boolean-like (true, false, 0, 1, '0', '1') |
| `array` | Value must be an array |
| `date` | Value must be a valid date string |
| `date_format:format` | Value must match the specified date format |
| `json` | Value must be a valid JSON string |

### String Validation Rules

| Rule | Description |
|------|-------------|
| `email` | Validates using `filter_var(…, FILTER_VALIDATE_EMAIL)` |
| `alpha` | Value must contain only alphabetic characters |
| `alpha_num` | Value must contain only alphanumeric characters |
| `url` | Value must be a valid URL |
| `uuid` | Value must be a valid UUID |
| `ip` | Value must be a valid IP address (v4 or v6) |
| `ipv4` | Value must be a valid IPv4 address |
| `ipv6` | Value must be a valid IPv6 address |
| `regex:pattern` | Value must match the given regex pattern |

### Size Rules

| Rule | Description |
|------|-------------|
| `min:x` | Minimum value/length/count |
| `max:x` | Maximum value/length/count |
| `size:x` | Exact value/length/count |
| `between:min,max` | Value must be between min and max |

### Comparison Rules

| Rule | Description |
|------|-------------|
| `in:a,b,c` | Value must be in the given list |
| `not_in:a,b,c` | Value must not be in the given list |
| `confirmed` | Field must have a matching `{field}_confirmation` field |
| `same:field` | Value must match the specified field |
| `different:field` | Value must differ from the specified field |

### File Rules

| Rule | Description |
|------|-------------|
| `file` | Value must be a valid file |
| `image` | Value must be an image (jpeg, png, gif, bmp, svg, webp) |
| `mimes:jpg,png,...` | File must have one of the specified MIME types |
| `max_file_size:kb` | File size must not exceed the specified kilobytes |

### Example

```php
$rules = [
    'name'     => 'required|string|max:100',
    'email'    => 'required|email',
    'age'      => 'required|integer|min:18|max:120',
    'website'  => 'nullable|url',
    'role'     => 'required|in:admin,user,guest',
    'password' => 'required|string|min:8|confirmed',
    'metadata' => 'nullable|json',
    'avatar'   => 'nullable|image|max_file_size:2048',
];
```

---

## Error Messages & Localization

The library includes a full message system with localization support.

### Using the Message Resolver

```php
use Vi\Validation\Messages\MessageResolver;
use Vi\Validation\Messages\Translator;

$resolver = new MessageResolver();

// Get a formatted error message
$message = $resolver->resolve('email', 'required');
// "The email field is required."

// Set custom messages
$resolver->setCustomMessages([
    'email.required' => 'Please provide your email address.',
    'required' => 'This field cannot be empty.',
]);

// Set custom attribute names
$resolver->setCustomAttributes([
    'email' => 'email address',
    'phone_number' => 'phone',
]);
```

### Changing Locale

```php
use Vi\Validation\Messages\Translator;

$translator = new Translator('en');
$translator->setLocale('ar');

// Add custom messages for a locale
$translator->addMessages([
    'required' => 'هذا الحقل مطلوب.',
], 'ar');
```

### Built-in Languages

- English (`en`)
- Arabic (`ar`)

Language files are located in `resources/lang/{locale}/validation.php`.

---

## Schema Caching

For improved performance, compiled schemas can be cached.

### In-Memory Cache

```php
use Vi\Validation\Cache\ArraySchemaCache;
use Vi\Validation\Execution\CompiledSchema;

$cache = new ArraySchemaCache();

// Store a schema
$cache->put('user-registration', $compiledSchema);

// Retrieve
$schema = $cache->get('user-registration');

// With TTL (seconds)
$cache->put('temporary', $schema, 3600);

// Clear cache
$cache->flush();
```

### File-Based Cache

```php
use Vi\Validation\Cache\FileSchemaCache;

$cache = new FileSchemaCache('/path/to/cache', 3600);

$cache->put('user-schema', $compiledSchema);
$schema = $cache->get('user-schema');
```

### Precompiled Validators

```php
use Vi\Validation\Compilation\PrecompiledValidator;

// Create and save
$precompiled = new PrecompiledValidator($schema, 'user-registration');
$precompiled->saveToFile('/path/to/validators/user-registration.compiled');

// Load and use
$validator = PrecompiledValidator::fromFile('/path/to/validators/user-registration.compiled');
$result = $validator->validate($data);
```

### Validator Compiler

```php
use Vi\Validation\Compilation\ValidatorCompiler;
use Vi\Validation\Cache\ArraySchemaCache;

$cache = new ArraySchemaCache();
$compiler = new ValidatorCompiler($cache, precompile: true, cachePath: '/path/to/compiled');

$schema = $compiler->compile('user-rules', $rules, function ($rules) {
    return $this->buildSchema($rules);
});
```

---

## Long-Running Process Support

The library provides first-class support for long-running processes like Laravel Octane, Swoole, and RoadRunner.

### Stateless Validator

Use `StatelessValidator` to ensure no state leaks between requests:

```php
use Vi\Validation\Runtime\StatelessValidator;

$validator = new StatelessValidator();

// Each call is isolated
$result = $validator->validate($schema, $data);
```

### Validator Pool

For high-concurrency environments, use a pool of validator instances:

```php
use Vi\Validation\Runtime\ValidatorPool;

$pool = new ValidatorPool(maxSize: 10);
$pool->onWorkerStart();

// Option 1: Manual acquire/release
$validator = $pool->acquire();
try {
    $result = $validator->validate($schema, $data);
} finally {
    $pool->release($validator);
}

// Option 2: Automatic management
$result = $pool->withValidator(function ($validator) use ($schema, $data) {
    return $validator->validate($schema, $data);
});
```

### Laravel Octane Integration

Register the Octane service provider in `config/app.php`:

```php
'providers' => [
    // ...
    Vi\Validation\Laravel\Octane\OctaneValidatorProvider::class,
],
```

The provider automatically:
- Manages validator pool lifecycle
- Resets context between requests
- Warms up validators on worker start

### Swoole Adapter

```php
use Vi\Validation\Runtime\Workers\SwooleAdapter;

$adapter = new SwooleAdapter();

$server->on('workerStart', fn() => $adapter->onWorkerStart());
$server->on('request', function ($req, $res) use ($adapter) {
    $adapter->onRequestStart();
    try {
        // Handle request
    } finally {
        $adapter->onRequestEnd();
    }
});
```

### RoadRunner Adapter

```php
use Vi\Validation\Runtime\Workers\RoadRunnerAdapter;

$adapter = new RoadRunnerAdapter();
$adapter->onWorkerStart();

while ($request = $worker->waitRequest()) {
    $adapter->handleRequest(function () use ($request) {
        // Process request
    });
}
```

---

## Configuration

The full configuration file (`config/fast-validation.php`):

```php
<?php

return [
    // Validation mode: 'parallel' or 'override'
    'mode' => 'parallel',

    // Cache configuration
    'cache' => [
        'enabled' => env('FAST_VALIDATION_CACHE', true),
        'driver' => env('FAST_VALIDATION_CACHE_DRIVER', 'array'), // array, file
        'ttl' => env('FAST_VALIDATION_CACHE_TTL', 3600),
        'path' => storage_path('framework/validation/cache'),
    ],

    // Compilation configuration
    'compilation' => [
        'precompile' => env('FAST_VALIDATION_PRECOMPILE', false),
        'cache_path' => storage_path('framework/validation/compiled'),
    ],

    // Performance options
    'performance' => [
        'fail_fast' => env('FAST_VALIDATION_FAIL_FAST', false),
        'max_errors' => env('FAST_VALIDATION_MAX_ERRORS', 100),
        'fast_path_rules' => env('FAST_VALIDATION_FAST_PATH', true),
    ],

    // Localization
    'localization' => [
        'locale' => env('FAST_VALIDATION_LOCALE', 'en'),
        'fallback_locale' => env('FAST_VALIDATION_FALLBACK_LOCALE', 'en'),
    ],

    // Long-running process support
    'runtime' => [
        'pooling' => env('FAST_VALIDATION_POOLING', false),
        'pool_size' => env('FAST_VALIDATION_POOL_SIZE', 10),
        'auto_detect' => env('FAST_VALIDATION_AUTO_DETECT', true),
    ],
];
```

---

## Testing

If you have the repo cloned locally, you can run the test suite with:

```bash
composer install

./vendor/bin/phpunit
```

This runs unit tests defined under `tests/`, including:
- Core type rules (numeric, boolean, array, date, json)
- String validation rules (alpha, url, uuid, ip, regex)
- Comparison rules (in, between, size, confirmed, same, different)
- File rules (file, image, mimes)
- Message system and localization
- Schema caching
- Laravel rule parser

---

## Project Structure

```
src/
├── Cache/                    # Schema caching
│   ├── SchemaCacheInterface.php
│   ├── ArraySchemaCache.php
│   └── FileSchemaCache.php
├── Compilation/              # Precompilation & optimization
│   ├── FastPathRules.php
│   ├── PrecompiledValidator.php
│   └── ValidatorCompiler.php
├── Execution/                # Core validation engine
├── Laravel/                  # Laravel integration
│   └── Octane/              # Octane support
├── Messages/                 # Error messages & i18n
│   ├── MessageBag.php
│   ├── MessageResolver.php
│   ├── Translator.php
│   └── TranslatorInterface.php
├── Rules/                    # Validation rules
├── Runtime/                  # Long-running process support
│   ├── ContextManager.php
│   ├── RuntimeAwareInterface.php
│   ├── StatelessValidator.php
│   ├── ValidatorPool.php
│   └── Workers/
│       ├── RoadRunnerAdapter.php
│       └── SwooleAdapter.php
└── Schema/                   # Schema building
resources/
└── lang/                     # Translation files
    ├── en/validation.php
    └── ar/validation.php
```

---

## Roadmap

**Completed:**
- Additional built-in rules (22 new rules)
- Rich Laravel rule mapping
- Advanced compilation strategies and micro-optimizations
- Detailed error messages with placeholder support
- Localization support (English, Arabic)
- Long-running process integration (Octane, Swoole, RoadRunner)
- Memory-efficient streaming validation API (generators, callbacks)

**Planned:**
- Additional language files
- Redis cache driver
- Rule dependency resolution
- Async validation support
