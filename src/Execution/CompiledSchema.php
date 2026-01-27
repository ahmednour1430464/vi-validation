<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Schema\FieldDefinition;

final class CompiledSchema
{
    /** @var list<CompiledField> */
    private array $fields;

    private ?ValidatorEngine $engine = null;

    /** @param list<CompiledField> $fields */
    private function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param array<string, FieldDefinition> $fieldDefinitions
     */
    public static function fromFieldDefinitions(array $fieldDefinitions): self
    {
        $compiled = [];

        foreach ($fieldDefinitions as $name => $definition) {
            $compiled[] = CompiledField::fromFieldDefinition($definition);
        }

        return new self($compiled);
    }

    /**
     * @return list<CompiledField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Validate data against this schema.
     *
     * @param array<string, mixed> $data
     */
    public function validate(array $data): ValidationResult
    {
        if ($this->engine === null) {
            $this->engine = new ValidatorEngine();
        }

        return $this->engine->validate($this, $data);
    }
}
