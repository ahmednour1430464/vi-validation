<?php

declare(strict_types=1);

namespace Vi\Validation\Schema;

use Vi\Validation\Rules\RuleInterface;

final class FieldDefinition
{
    private string $name;

    /** @var list<RuleInterface> */
    private array $rules = [];

    private SchemaBuilder $builder;

    public function __construct(string $name, SchemaBuilder $builder)
    {
        $this->name = $name;
        $this->builder = $builder;
    }

    public function required(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredRule();
        return $this;
    }

    public function nullable(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\NullableRule();
        return $this;
    }

    public function string(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\StringTypeRule();
        return $this;
    }

    public function integer(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\IntegerTypeRule();
        return $this;
    }

    public function min(int|float $min): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MinRule($min);
        return $this;
    }

    public function max(int|float $max): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MaxRule($max);
        return $this;
    }

    public function email(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\EmailRule();
        return $this;
    }

    public function rules(RuleInterface ...$rules): self
    {
        array_push($this->rules, ...$rules);
        return $this;
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

    public function end(): SchemaBuilder
    {
        return $this->builder;
    }
}
