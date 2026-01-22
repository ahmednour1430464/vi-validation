<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Rules\RuleInterface;
use Vi\Validation\Schema\FieldDefinition;

final class CompiledField
{
    private string $name;

    /** @var list<RuleInterface> */
    private array $rules;

    /**
     * @param list<RuleInterface> $rules
     */
    private function __construct(string $name, array $rules)
    {
        $this->name = $name;
        $this->rules = $rules;
    }

    public static function fromFieldDefinition(FieldDefinition $definition): self
    {
        return new self($definition->getName(), $definition->getRules());
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return list<RuleInterface>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
