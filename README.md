# vi/validation 🚀

> **The High-Performance PHP Validation Library**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vi/validation.svg?style=flat-square)](https://packagist.org/packages/vi/validation)
[![Total Downloads](https://img.shields.io/packagist/dt/vi/validation.svg?style=flat-square)](https://packagist.org/packages/vi/validation)

**vi/validation** is a blazing fast, memory-efficient validation library designed for high-performance applications. Whether you are processing large datasets, building high-frequency APIs, or running long-lived processes (Octane, Swoole), this library handles it with minimal overhead.

---

## ⚡ Performance at a Glance

Stop trading performance for convenience. **vi/validation** delivers **17x to 34x speedups** compared to standard Laravel validation.

| Scenario | Rows | FastValidator 🚀 | Laravel Validator | Speedup | Throughput |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Complex Rules** | 100,000 | **1.48s** | 48.83s | **33.0x** | ~67,713 req/s |
| **Complex Rules** | 10,000 | **0.14s** | 4.83s | **34.1x** | ~70,696 req/s |
| **Medium Rules** | 100,000 | **1.36s** | 39.11s | **28.7x** | ~73,365 req/s |
| **Simple Rules** | 100,000 | **1.14s** | 19.66s | **17.2x** | ~87,739 req/s |

> *Benchmarks run on PHP 8.2.29. "Complex Rules" include nested fields, regex, conditional requirements, and type checks.*

---

## 🌟 Why vi/validation?

- **Compile Once, Validate Many**: Schemas are compiled into optimized execution plans, eliminating repetitive parsing overhead.
- **Native Code Generation**: Automatically generates raw PHP code for your validation rules, bypassing reflection and dynamic calls entirely.
- **O(1) Memory Usage**: Stream validated data from massive datasets (CSVs, JSON streams) without ever loading everything into RAM.
- **Laravel Compatible**: Drop-in support for Laravel rules. Use it alongside standard validators or replace them entirely.
- **Octane Ready**: First-class support for Laravel Octane, Swoole, and RoadRunner with stateless validation and worker pools.

---

## 📦 Installation

```bash
composer require vi/validation
```

---

## 🚀 Quick Start

### 1. Standalone PHP

Ideal for scripts, ETL jobs, or non-Laravel projects.

```php
use Vi\Validation\Validator;
use Vi\Validation\SchemaValidator;

// 1. Define & Compile Schema
$schema = Validator::schema()
    ->field('email')->required()->email()->end()
    ->field('age')->required()->integer()->min(18)->end()
    ->compile();

// 2. Create Validator
$validator = new SchemaValidator($schema);

// 3. Validate
$data = ['email' => 'user@example.com', 'age' => 25];
$result = $validator->validate($data);

if ($result->isValid()) {
    // success
} else {
    print_r($result->messages());
}
```

### 2. Laravel (Parallel Mode) - *Recommended*

Use `vi/validation` explicitly where you need performance, keeping standard Laravel validation elsewhere.

```php
use Vi\Validation\Laravel\Facades\FastValidator;

public function store()
{
    // Use the Facade just like Validator::make()
    $validator = FastValidator::make(request()->all(), [
        'email' => 'required|email',
        'name'  => 'required|string|max:100',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $validated = $validator->validated();
    // ...
}
```

### 3. Laravel (Override Mode)

Transparently route standard `Validator::make()` calls through the fast engine.

1. Publish config: `php artisan vendor:publish --tag=config --provider="Vi\Validation\Laravel\FastValidationServiceProvider"`
2. Edit `config/fast-validation.php`:
   ```php
   'mode' => 'override',
   ```
3. Use Laravel validation as usual. Supported rules will be accelerated automatically.

### 4. Streaming with Laravel Facade

Process large datasets memory-efficiently using the Laravel-style API. The `FastValidator::make()` method accepts iterables (generators, iterators) and provides a `stream()` method.

```php
use Vi\Validation\Laravel\Facades\FastValidator;

// Assume $inputs is a Generator or large array
$validator = FastValidator::make($inputs, $rules);

$validCount = 0;
$invalidCount = 0;
$totalErrors = 0;

// Stream results one by one - O(1) Memory Usage
foreach ($validator->stream() as $index => $result) {
    if ($result->isValid()) {
        $validCount++;
        // Process valid row...
    } else {
        $invalidCount++;
        $totalErrors += count($result->errors());
        // Log errors...
    }
}
```

---

## 📖 Key Features & Documentation

### 🌊 Streaming & Large Datasets

Validating 100,000 rows? Don't crash your server. Use `stream()` to process records one by one with constant memory usage.

```php
$schema = Validator::schema()
    ->field('id')->required()->integer()->end()
    ->compile();

$validator = new SchemaValidator($schema);

// Zero memory spikes, even with 1M+ rows
foreach ($validator->stream($largeDataset) as $result) {
    if (!$result->isValid()) {
        // Log error
    }
}
```

### 🛠 Supported Rules

We support a comprehensive set of almost all standard Laravel rules.

| Category | Rules |
| :--- | :--- |
| **Core & Presence** | `required`, `nullable`, `filled`, `present`, `missing`, `bail`, `sometimes` |
| **Conditionals** | `required_if`, `required_unless`, `required_with`, `required_with_all`, `required_without`, `required_without_all`, `required_if_accepted`, `missing_if`, `missing_unless`, `missing_with`, `missing_with_all`, `prohibited`, `prohibited_if`, `prohibited_unless`, `prohibits`, `exclude`, `exclude_if`, `exclude_unless`, `exclude_with`, `exclude_without` |
| **Types** | `string`, `integer`, `numeric`, `boolean`, `array`, `list`, `date`, `json`, `enum`, `decimal` |
| **Strings** | `email`, `url`, `active_url`, `ip`, `ipv4`, `ipv6`, `mac_address`, `uuid`, `ulid`, `alpha`, `alpha_dash`, `alpha_num`, `ascii`, `regex`, `not_regex`, `starts_with`, `ends_with`, `doesnt_start_with`, `doesnt_end_with`, `lowercase`, `uppercase` |
| **Numbers & Size** | `min`, `max`, `size`, `between`, `digits`, `digits_between`, `multiple_of` |
| **Comparison** | `in`, `not_in`, `gt`, `gte`, `lt`, `lte`, `confirmed`, `same`, `different` |
| **Dates** | `date_format`, `date_equals`, `after`, `after_or_equal`, `before`, `before_or_equal`, `timezone` |
| **Arrays** | `distinct`, `required_array_keys` |
| **Files** | `file`, `image`, `mimes`, `mimetypes`, `min_file_size`, `max_file_size`, `dimensions` |
| **Acceptance** | `accepted`, `accepted_if`, `declined`, `declined_if` |
| **Database** | `exists`, `unique` |
| **Auth** | `password`, `current_password` |
| **Others** | `country`, `language` |

### 🌐 Localization

Fully localized error messages. English and Arabic are built-in.

```php
use Vi\Validation\Messages\Translator;

$translator = new Translator('ar'); // Switch to Arabic
```

---

## ⚙️ Configuration

Publish the config file to tweak performance settings:

```php
// config/fast-validation.php
return [
    'mode' => 'parallel', // 'parallel' or 'override'
    
    'performance' => [
        'fail_fast' => false, // Stop at first error per field
        'max_errors' => 100,  // Stop validation after N errors
    ],
    
    'compilation' => [
        'precompile' => true, // Save compiled schemas to disk
    ],
];
```

---

## 🧪 Testing

Run the test suite to verify everything is working on your machine:

```bash
composer install
./vendor/bin/phpunit
```

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
