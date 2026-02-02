<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use ReflectionClass;
use InvalidArgumentException;

/**
 * Registry for validation rules to facilitate string-to-rule mapping.
 */
final class RuleRegistry
{
    /** @var array<string, class-string<RuleInterface>> */
    private array $rules = [];

    /** @var array<string, string> */
    private array $aliases = [];

    /**
     * Register a single rule class by reading its #[RuleName] attribute.
     *
     * @param class-string<RuleInterface> $class
     */
    public function register(string $class): void
    {
        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getAttributes(RuleName::class);

        if (empty($attributes)) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" does not have the #[RuleName] attribute.',
                $class
            ));
        }

        /** @var RuleName $attribute */
        $attribute = $attributes[0]->newInstance();
        $name = $attribute->name;

        $this->rules[$name] = $class;

        foreach ($attribute->aliases as $alias) {
            $this->aliases[$alias] = $name;
        }
    }

    /**
     * Register multiple rule classes.
     *
     * @param array<class-string<RuleInterface>> $classes
     */
    public function registerMany(array $classes): void
    {
        foreach ($classes as $class) {
            $this->register($class);
        }
    }

    /**
     * Check if a rule exists by name, alias, or RuleId.
     */
    public function has(string|RuleId $name): bool
    {
        if ($name instanceof RuleId) {
            $name = $name->value;
        }

        return isset($this->rules[$name]) || isset($this->aliases[$name]);
    }

    /**
     * Get the class string for a rule by name, alias, or RuleId.
     *
     * @return class-string<RuleInterface>|null
     */
    public function get(string|RuleId $name): ?string
    {
        if ($name instanceof RuleId) {
            $name = $name->value;
        }

        if (isset($this->rules[$name])) {
            return $this->rules[$name];
        }

        if (isset($this->aliases[$name])) {
            $primaryName = $this->aliases[$name];
            return $this->rules[$primaryName] ?? null;
        }

        return null;
    }

    /**
     * Register all built-in rules.
     */
    public function registerBuiltInRules(): void
    {
        $this->registerMany([
            AcceptedRule::class,
            AcceptedIfRule::class,
            ActiveUrlRule::class,
            AfterOrEqualRule::class,
            AfterRule::class,
            AlphaDashRule::class,
            AlphaRule::class,
            AlphanumericRule::class,
            ArrayRule::class,
            BailRule::class,
            BeforeOrEqualRule::class,
            BeforeRule::class,
            AsciiRule::class,
            BetweenRule::class,
            BooleanRule::class,
            ClosureRule::class,
            ConfirmedRule::class,
            CountryRule::class,
            CurrentPasswordRule::class,
            DateEqualsRule::class,
            DateFormatRule::class,
            DateRule::class,
            DecimalRule::class,
            DeclinedRule::class,
            DeclinedIfRule::class,
            DifferentRule::class,
            DigitsBetweenRule::class,
            DigitsRule::class,
            DimensionsRule::class,
            DistinctRule::class,
            DoesntEndWithRule::class,
            DoesntStartWithRule::class,
            EmailRule::class,
            EndsWithRule::class,
            EnumRule::class,
            ExcludeRule::class,
            ExcludeIfRule::class,
            ExcludeUnlessRule::class,
            ExcludeWithRule::class,
            ExcludeWithoutRule::class,
            ExistsRule::class,
            ExtensionsRule::class,
            FileRule::class,
            FilledRule::class,
            GreaterThanOrEqualRule::class,
            GreaterThanRule::class,
            ImageRule::class,
            InRule::class,
            IntegerTypeRule::class,
            IpRule::class,
            JsonRule::class,
            LanguageRule::class,
            LessThanOrEqualRule::class,
            LessThanRule::class,
            ListRule::class,
            LowercaseRule::class,
            MacAddressRule::class,
            MaxFileSizeRule::class,
            MaxRule::class,
            MimesRule::class,
            MimetypesRule::class,
            MinRule::class,
            MinFileSizeRule::class,
            MissingRule::class,
            MissingIfRule::class,
            MissingUnlessRule::class,
            MissingWithRule::class,
            MissingWithAllRule::class,
            MultipleOfRule::class,
            NotInRule::class,
            NotRegexRule::class,
            NullableRule::class,
            NumericRule::class,
            PasswordRule::class,
            PresentRule::class,
            ProhibitedIfRule::class,
            ProhibitedRule::class,
            ProhibitedUnlessRule::class,
            ProhibitsRule::class,
            RegexRule::class,
            RequiredArrayKeysRule::class,
            RequiredIfRule::class,
            RequiredIfAcceptedRule::class,
            RequiredRule::class,
            RequiredUnlessRule::class,
            RequiredWithAllRule::class,
            RequiredWithRule::class,
            RequiredWithoutAllRule::class,
            RequiredWithoutRule::class,
            SameRule::class,
            SizeRule::class,
            SometimesRule::class,
            StartsWithRule::class,
            StringTypeRule::class,
            TimezoneRule::class,
            UlidRule::class,
            UniqueRule::class,
            UppercaseRule::class,
            UrlRule::class,
            UuidRule::class,
        ]);
    }
}
