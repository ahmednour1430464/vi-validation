<?php

declare(strict_types=1);

namespace Vi\Validation\Schema;

use Vi\Validation\Execution\CompiledSchema;

final class SchemaBuilder
{
    /** @var array<string, FieldDefinition> */
    private array $fields = [];

    public function field(string $name): FieldDefinition
    {
        if (!isset($this->fields[$name])) {
            $this->fields[$name] = new FieldDefinition($name, $this);
        }

        return $this->fields[$name];
    }

    public function compile(): CompiledSchema
    {
        return CompiledSchema::fromFieldDefinitions($this->fields);
    }
}
