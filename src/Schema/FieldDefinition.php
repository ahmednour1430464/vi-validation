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

    public function bail(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\BailRule();
        return $this;
    }

    public function sometimes(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\SometimesRule();
        return $this;
    }

    public function string(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\StringTypeRule();
        return $this;
    }

    public function str(): self
    {
        return $this->string();
    }

    public function integer(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\IntegerTypeRule();
        return $this;
    }

    public function int(): self
    {
        return $this->integer();
    }

    public function decimal(int $min, ?int $max = null): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DecimalRule($min, $max);
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

    public function list(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ListRule();
        return $this;
    }

    public function distinct(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DistinctRule();
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

    public function gt(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\GreaterThanRule($otherField);
        return $this;
    }

    public function gte(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\GreaterThanOrEqualRule($otherField);
        return $this;
    }

    public function lt(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\LessThanRule($otherField);
        return $this;
    }

    public function lte(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\LessThanOrEqualRule($otherField);
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

    public function exists(string $table, string $column = 'id', array $extraConstraints = []): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExistsRule($table, $column, $extraConstraints);
        return $this;
    }

    public function unique(string $table, string $column = 'id', mixed $ignoreId = null, string $idColumn = 'id', array $extraConstraints = []): self
    {
        $this->rules[] = new \Vi\Validation\Rules\UniqueRule($table, $column, $ignoreId, $idColumn, $extraConstraints);
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

    public function minFileSize(int $kb): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MinFileSizeRule($kb);
        return $this;
    }

    public function mimetypes(string ...$types): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MimetypesRule(...$types);
        return $this;
    }

    public function extensions(string ...$extensions): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExtensionsRule(...$extensions);
        return $this;
    }

    public function dimensions(array $constraints): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DimensionsRule($constraints);
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

    public function country(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\CountryRule();
        return $this;
    }

    public function language(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\LanguageRule();
        return $this;
    }

    public function ascii(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\AsciiRule();
        return $this;
    }

    public function uppercase(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\UppercaseRule();
        return $this;
    }

    public function enum(string $enumClass): self
    {
        $this->rules[] = new \Vi\Validation\Rules\EnumRule($enumClass);
        return $this;
    }

    public function password(?callable $callback = null): self
    {
        $rule = new \Vi\Validation\Rules\PasswordRule();
        if ($callback) {
            $callback($rule);
        }
        $this->rules[] = $rule;
        return $this;
    }

    public function currentPassword(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\CurrentPasswordRule();
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

    public function digits(int $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DigitsRule($value);
        return $this;
    }

    public function digitsBetween(int $min, int $max): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DigitsBetweenRule($min, $max);
        return $this;
    }

    public function startsWith(string ...$needles): self
    {
        $this->rules[] = new \Vi\Validation\Rules\StartsWithRule($needles);
        return $this;
    }

    public function endsWith(string ...$needles): self
    {
        $this->rules[] = new \Vi\Validation\Rules\EndsWithRule($needles);
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

    public function requiredIf(string $otherField, array $values): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredIfRule($otherField, $values);
        return $this;
    }

    public function requiredIfAccepted(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredIfAcceptedRule($otherField);
        return $this;
    }

    public function requiredUnless(string $otherField, array $values): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredUnlessRule($otherField, $values);
        return $this;
    }

    public function requiredWith(string ...$others): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredWithRule($others);
        return $this;
    }

    public function requiredWithAll(string ...$others): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredWithAllRule($others);
        return $this;
    }

    public function requiredWithout(string ...$others): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredWithoutRule($others);
        return $this;
    }

    public function requiredWithoutAll(string ...$others): self
    {
        $this->rules[] = new \Vi\Validation\Rules\RequiredWithoutAllRule($others);
        return $this;
    }

    public function present(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\PresentRule();
        return $this;
    }

    public function filled(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\FilledRule();
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

    public function acceptedIf(string $otherField, mixed $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\AcceptedIfRule($otherField, $value);
        return $this;
    }

    public function declinedIf(string $otherField, mixed $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\DeclinedIfRule($otherField, $value);
        return $this;
    }

    public function prohibits(string ...$fields): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ProhibitsRule(...$fields);
        return $this;
    }

    public function missing(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MissingRule();
        return $this;
    }

    public function missingIf(string $otherField, mixed $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MissingIfRule($otherField, $value);
        return $this;
    }

    public function missingUnless(string $otherField, mixed $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MissingUnlessRule($otherField, $value);
        return $this;
    }

    public function missingWith(string ...$others): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MissingWithRule(...$others);
        return $this;
    }

    public function missingWithAll(string ...$others): self
    {
        $this->rules[] = new \Vi\Validation\Rules\MissingWithAllRule(...$others);
        return $this;
    }

    public function exclude(): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExcludeRule();
        return $this;
    }

    public function excludeIf(string $otherField, mixed $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExcludeIfRule($otherField, $value);
        return $this;
    }

    public function excludeUnless(string $otherField, mixed $value): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExcludeUnlessRule($otherField, $value);
        return $this;
    }

    public function excludeWith(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExcludeWithRule($otherField);
        return $this;
    }

    public function excludeWithout(string $otherField): self
    {
        $this->rules[] = new \Vi\Validation\Rules\ExcludeWithoutRule($otherField);
        return $this;
    }

    public function when($condition, callable $onTrue, ?callable $onFalse = null): self
    {
        // We create a temporary builder to collect rules for onTrue and onFalse
        $trueBuilder = new SchemaBuilder();
        $trueField = new FieldDefinition($this->name, $trueBuilder);
        $onTrue($trueField);
        $trueRules = $trueField->getRules();

        $falseRules = [];
        if ($onFalse) {
            $falseBuilder = new SchemaBuilder();
            $falseField = new FieldDefinition($this->name, $falseBuilder);
            $onFalse($falseField);
            $falseRules = $falseField->getRules();
        }

        $this->rules[] = new \Vi\Validation\Rules\ConditionalRule($condition, $trueRules, $falseRules);
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
