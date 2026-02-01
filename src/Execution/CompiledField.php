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

    private bool $isNullable;

    private bool $isNested;
    private ?string $parentField = null;
    private ?string $childField = null;

    /**
     * @param list<RuleInterface> $rules
     */
    private function __construct(string $name, array $rules)
    {
        $this->name = $name;

        if (strpos($name, '.') !== false) {
            $this->isNested = true;
            [$this->parentField, $this->childField] = explode('.', $name, 2);
        } else {
            $this->isNested = false;
        }

        $this->isNullable = false;
        foreach ($rules as $rule) {
            if ($rule instanceof \Vi\Validation\Rules\NullableRule) {
                $this->isNullable = true;
                break;
            }
        }

        // Optimization: Remove NullableRule from runtime rules to avoid checking it during validation
        $this->rules = array_values(array_filter($rules, fn ($r) => !($r instanceof \Vi\Validation\Rules\NullableRule)));
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

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function getValue(array $data): mixed
    {
        if (!$this->isNested) {
            return $data[$this->name] ?? null;
        }

        $parent = $data[$this->parentField] ?? null;
        if (!is_array($parent)) {
            return null;
        }

        return $parent[$this->childField] ?? null;
    }
}
