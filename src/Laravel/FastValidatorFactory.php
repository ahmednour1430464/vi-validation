<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Illuminate\Contracts\Container\Container;
use Vi\Validation\SchemaValidator;

final class FastValidatorFactory
{
    /** @var array<string, mixed> */
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Mirror Laravel's Validator::make signature at a high level.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     */
    public function make(array $data, array $rules): FastValidatorWrapper
    {
        $schemaValidator = $this->buildSchemaValidator($rules);

        return new FastValidatorWrapper($schemaValidator, $data);
    }

    /**
     * @param array<string, mixed> $rules
     */
    private function buildSchemaValidator(array $rules): SchemaValidator
    {
        $parser = new LaravelRuleParser();

        return SchemaValidator::build(function ($schema) use ($rules, $parser) {
            foreach ($rules as $field => $definition) {
                $fieldBuilder = $schema->field($field);
                $parsedRules = $parser->parse($definition);
                $fieldBuilder->rules(...$parsedRules);
            }
        });
    }
}
