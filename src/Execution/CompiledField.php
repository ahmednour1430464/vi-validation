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
    private bool $isBail;
    private bool $isSometimes;
    private bool $isNested;
    private ?string $parentField = null;
    private ?string $childField = null;

    private bool $isAlwaysExcluded = false;
    /** @var list<RuleInterface> */
    private array $exclusionRules = [];

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
        $this->isBail = false;
        $this->isSometimes = false;

        foreach ($rules as $rule) {
            if ($rule instanceof \Vi\Validation\Rules\NullableRule) {
                $this->isNullable = true;
            } elseif ($rule instanceof \Vi\Validation\Rules\BailRule) {
                $this->isBail = true;
            } elseif ($rule instanceof \Vi\Validation\Rules\SometimesRule) {
                $this->isSometimes = true;
            } elseif ($rule instanceof \Vi\Validation\Rules\ExcludeRule) {
                $this->isAlwaysExcluded = true;
            } elseif (
                $rule instanceof \Vi\Validation\Rules\ExcludeIfRule ||
                $rule instanceof \Vi\Validation\Rules\ExcludeUnlessRule ||
                $rule instanceof \Vi\Validation\Rules\ExcludeWithRule ||
                $rule instanceof \Vi\Validation\Rules\ExcludeWithoutRule
            ) {
                $this->exclusionRules[] = $rule;
            }
        }

        // Optimization: Remove marker rules from runtime rules to avoid checking them during validation
        $markerClasses = [
            \Vi\Validation\Rules\NullableRule::class,
            \Vi\Validation\Rules\BailRule::class,
            \Vi\Validation\Rules\SometimesRule::class,
            \Vi\Validation\Rules\ExcludeRule::class,
            \Vi\Validation\Rules\ExcludeIfRule::class,
            \Vi\Validation\Rules\ExcludeUnlessRule::class,
            \Vi\Validation\Rules\ExcludeWithRule::class,
            \Vi\Validation\Rules\ExcludeWithoutRule::class,
        ];

        $this->rules = array_values(array_filter($rules, function ($r) use ($markerClasses) {
            foreach ($markerClasses as $class) {
                if ($r instanceof $class) {
                    return false;
                }
            }
            return true;
        }));
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

    public function isBail(): bool
    {
        return $this->isBail;
    }

    public function isSometimes(): bool
    {
        return $this->isSometimes;
    }

    public function shouldExclude(ValidationContext $context): bool
    {
        if ($this->isAlwaysExcluded) {
            return true;
        }

        foreach ($this->exclusionRules as $rule) {
            if (method_exists($rule, 'shouldExclude') && $rule->shouldExclude($context)) {
                return true;
            }
        }

        return false;
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
