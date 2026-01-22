<?php

declare(strict_types=1);

namespace Vi\Validation;

use Vi\Validation\Execution\CompiledSchema;
use Vi\Validation\Execution\ValidatorEngine;
use Vi\Validation\Execution\ValidationResult;
use Vi\Validation\Schema\SchemaBuilder;

final class SchemaValidator
{
    private CompiledSchema $schema;
    private ValidatorEngine $engine;

    public function __construct(CompiledSchema $schema, ?ValidatorEngine $engine = null)
    {
        $this->schema = $schema;
        $this->engine = $engine ?? new ValidatorEngine();
    }

    public static function build(callable $definition): self
    {
        $builder = new SchemaBuilder();
        $definition($builder);

        return new self($builder->compile());
    }

    public function validate(array $data): ValidationResult
    {
        return $this->engine->validate($this->schema, $data);
    }

    public function validateMany(iterable $rows): array
    {
        $results = [];

        foreach ($rows as $index => $row) {
            $results[] = $this->validate($row);
        }

        return $results;
    }
}
