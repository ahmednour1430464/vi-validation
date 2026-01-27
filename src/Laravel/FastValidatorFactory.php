<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Vi\Validation\Cache\ArraySchemaCache;
use Vi\Validation\Cache\FileSchemaCache;
use Vi\Validation\Cache\SchemaCacheInterface;
use Vi\Validation\Execution\CompiledSchema;
use Vi\Validation\Execution\ValidatorEngine;
use Vi\Validation\Messages\MessageResolver;
use Vi\Validation\Messages\Translator;
use Vi\Validation\Schema\SchemaBuilder;
use Vi\Validation\SchemaValidator;

final class FastValidatorFactory
{
    /** @var array<string, mixed> */
    private array $config;

    private ?SchemaCacheInterface $cache = null;

    private ?ValidatorEngine $engine = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->initializeCache();
        $this->initializeEngine();
    }

    /**
     * Mirror Laravel's Validator::make signature at a high level.
     *
     * @param iterable<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, string> $messages Custom error messages
     * @param array<string, string> $attributes Custom attribute names
     */
    public function make(
        iterable $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): FastValidatorWrapper {
        $schemaValidator = $this->buildSchemaValidator($rules, $messages, $attributes);

        return new FastValidatorWrapper($schemaValidator, $data);
    }

    /**
     * Get or create schema cache instance.
     */
    public function getCache(): ?SchemaCacheInterface
    {
        return $this->cache;
    }

    /**
     * @param array<string, mixed> $rules
     * @param array<string, string> $messages
     * @param array<string, string> $attributes
     */
    private function buildSchemaValidator(
        array $rules,
        array $messages = [],
        array $attributes = []
    ): SchemaValidator {
        $cacheKey = $this->generateCacheKey($rules);
        
        // Try to get from cache
        if ($this->cache !== null) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $this->createValidatorWithSchema($cached, $messages, $attributes);
            }
        }

        // Build schema
        $parser = new LaravelRuleParser();
        $builder = new SchemaBuilder();

        foreach ($rules as $field => $definition) {
            $fieldBuilder = $builder->field($field);
            $parsedRules = $parser->parse($definition);
            $fieldBuilder->rules(...$parsedRules);
        }

        $schema = $builder->compile();

        // Cache the schema
        if ($this->cache !== null) {
            $ttl = $this->config['cache']['ttl'] ?? 3600;
            $this->cache->put($cacheKey, $schema, $ttl);
        }

        return $this->createValidatorWithSchema($schema, $messages, $attributes);
    }

    private function createValidatorWithSchema(
        CompiledSchema $schema,
        array $messages = [],
        array $attributes = []
    ): SchemaValidator {
        // Create engine with custom messages/attributes
        $engine = $this->createEngine($messages, $attributes);

        return new SchemaValidator($schema, $engine);
    }

    private function createEngine(array $messages = [], array $attributes = []): ValidatorEngine
    {
        $locale = $this->config['localization']['locale'] ?? 'en';
        $fallbackLocale = $this->config['localization']['fallback_locale'] ?? 'en';

        $translator = new Translator($locale);
        $translator->setFallbackLocale($fallbackLocale);

        $messageResolver = new MessageResolver($translator);

        if (!empty($messages)) {
            $messageResolver->setCustomMessages($messages);
        }

        if (!empty($attributes)) {
            $messageResolver->setCustomAttributes($attributes);
        }

        $failFast = $this->config['performance']['fail_fast'] ?? false;
        $maxErrors = $this->config['performance']['max_errors'] ?? 100;

        return new ValidatorEngine($messageResolver, $failFast, $maxErrors);
    }

    private function initializeCache(): void
    {
        $cacheConfig = $this->config['cache'] ?? [];
        
        if (!($cacheConfig['enabled'] ?? true)) {
            return;
        }

        $driver = $cacheConfig['driver'] ?? 'array';

        if ($driver === 'file') {
            $path = $cacheConfig['path'] ?? sys_get_temp_dir() . '/vi-validation';
            $ttl = $cacheConfig['ttl'] ?? 3600;
            $this->cache = new FileSchemaCache($path, $ttl);
        } else {
            $this->cache = new ArraySchemaCache();
        }
    }

    private function initializeEngine(): void
    {
        $failFast = $this->config['performance']['fail_fast'] ?? false;
        $maxErrors = $this->config['performance']['max_errors'] ?? 100;
        $locale = $this->config['localization']['locale'] ?? 'en';

        $translator = new Translator($locale);
        $messageResolver = new MessageResolver($translator);

        $this->engine = new ValidatorEngine($messageResolver, $failFast, $maxErrors);
    }

    private function generateCacheKey(array $rules): string
    {
        return md5(serialize($rules));
    }
}
