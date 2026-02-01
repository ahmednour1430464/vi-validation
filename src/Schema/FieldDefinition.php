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

    public function numeric(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\NumericRule();
        return $this;
    }

    public function boolean(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\BooleanRule();
        return $this;
    }

    public function bool(): self
    {
        return $this->boolean();
    }

    public function array(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ArrayRule();
        return $this;
    }

    public function date(?string $format = null): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DateRule($format);
        return $this;
    }

    public function json(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\JsonRule();
        return $this;
    }

    public function alpha(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\AlphaRule();
        return $this;
    }

    public function alphaNum(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\AlphanumericRule();
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

    public function url(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\UrlRule();
        return $this;
    }

    public function uuid(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\UuidRule();
        return $this;
    }

    public function ip(?string $version = null): self
    {
        $this->rules[] = new \Vi\Validation\Rules\IpRule($version);
        return $this;
    }

    public function regex(string $pattern): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RegexRule($pattern);
        return $this;
    }

    public function size(int|float $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\SizeRule($value);
        return $this;
    }

    public function between(int|float $min, int|float $max): self
    {
        $this->rules[] = new \Vi\Validation\Rules\BetweenRule($min, $max);
        return $this;
    }

    public function in(mixed ...$values): self
    {
        $this->rules[] = new \Vi\Validation\Rules\InRule($values);
        return $this;
    }

    public function notIn(mixed ...$values): self
    {
        $this->rules[] = new \Vi\Validation\Rules\NotInRule($values);
        return $this;
    }

    public function confirmed(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ConfirmedRule();
        return $this;
    }

    public function same(string $field): self
    {
        $this->rules[] = new \Vi\Validation\Rules\SameRule($field);
        return $this;
    }

    public function different(string $field): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DifferentRule($field);
        return $this;
    }

    public function file(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\FileRule();
        return $this;
    }

    public function image(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ImageRule();
        return $this;
    }

    public function mimes(string ...$types): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MimesRule($types);
        return $this;
    }

    public function maxFileSize(int $kb): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MaxFileSizeRule($kb);
        return $this;
    }

    public function rules(RuleInterface ...$rules): self
    {
        array_push($this->rules, ...$rules);
        return $this;
    }

    // Phase 1: String & Format
    public function activeUrl(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ActiveUrlRule();
        return $this;
    }

    public function alphaDash(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\AlphaDashRule();
        return $this;
    }

    public function macAddress(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MacAddressRule();
        return $this;
    }

    public function ulid(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\UlidRule();
        return $this;
    }

    public function uppercase(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\UppercaseRule();
        return $this;
    }

    public function lowercase(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\LowercaseRule();
        return $this;
    }

    // Phase 2: Date & Numeric
    public function dateFormat(string $format): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DateFormatRule($format);
        return $this;
    }

    public function dateEquals(string $date): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DateEqualsRule($date);
        return $this;
    }

    public function multipleOf(int|float $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MultipleOfRule($value);
        return $this;
    }

    // Phase 3: Advanced Logic
    public function notRegex(string $pattern): self
    {
        $this->rules[] = new \Vi\Validation\Rules\NotRegexRule($pattern);
        return $this;
    }

    public function doesntStartWith(string ...$needles): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DoesntStartWithRule(...$needles);
        return $this;
    }

    public function doesntEndWith(string ...$needles): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DoesntEndWithRule(...$needles);
        return $this;
    }

    public function timezone(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\TimezoneRule();
        return $this;
    }

    public function requiredArrayKeys(string ...$keys): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredArrayKeysRule(...$keys);
        return $this;
    }

    public function prohibitedIf(string $otherField, array $values): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ProhibitedIfRule($otherField, $values);
        return $this;
    }

    public function prohibitedUnless(string $otherField, array $values): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ProhibitedUnlessRule($otherField, $values);
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

    /**
     * Shortcut to define another field (fluent chain support).
     */
    public function field(string $name): self
    {
        return $this->builder->field($name);
    }

    /**
     * Compile the schema (shortcut for chaining).
     */
    public function compile(): \Vi\Validation\Execution\CompiledSchema
    {
        return $this->builder->compile();
    }

    public function end(): SchemaBuilder
    {
        return $this->builder;
    }
}
