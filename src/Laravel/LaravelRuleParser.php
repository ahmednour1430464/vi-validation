<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Closure;
use Vi\Validation\Rules\AcceptedRule;
use Vi\Validation\Rules\ActiveUrlRule;
use Vi\Validation\Rules\AfterOrEqualRule;
use Vi\Validation\Rules\AfterRule;
use Vi\Validation\Rules\AlphaDashRule;
use Vi\Validation\Rules\AlphanumericRule;
use Vi\Validation\Rules\AlphaRule;
use Vi\Validation\Rules\ArrayRule;
use Vi\Validation\Rules\BeforeOrEqualRule;
use Vi\Validation\Rules\BeforeRule;
use Vi\Validation\Rules\BetweenRule;
use Vi\Validation\Rules\BooleanRule;
use Vi\Validation\Rules\ClosureRule;
use Vi\Validation\Rules\ConfirmedRule;
use Vi\Validation\Rules\DateEqualsRule;
use Vi\Validation\Rules\DateRule;
use Vi\Validation\Rules\DeclinedRule;
use Vi\Validation\Rules\DifferentRule;
use Vi\Validation\Rules\DigitsBetweenRule;
use Vi\Validation\Rules\DigitsRule;
use Vi\Validation\Rules\DistinctRule;
use Vi\Validation\Rules\DoesntEndWithRule;
use Vi\Validation\Rules\DoesntStartWithRule;
use Vi\Validation\Rules\EmailRule;
use Vi\Validation\Rules\EndsWithRule;
use Vi\Validation\Rules\FilledRule;
use Vi\Validation\Rules\FileRule;
use Vi\Validation\Rules\GreaterThanOrEqualRule;
use Vi\Validation\Rules\GreaterThanRule;
use Vi\Validation\Rules\ImageRule;
use Vi\Validation\Rules\InRule;
use Vi\Validation\Rules\IntegerTypeRule;
use Vi\Validation\Rules\IpRule;
use Vi\Validation\Rules\JsonRule;
use Vi\Validation\Rules\LessThanOrEqualRule;
use Vi\Validation\Rules\LessThanRule;
use Vi\Validation\Rules\LowercaseRule;
use Vi\Validation\Rules\MacAddressRule;
use Vi\Validation\Rules\MaxFileSizeRule;
use Vi\Validation\Rules\MaxRule;
use Vi\Validation\Rules\MimesRule;
use Vi\Validation\Rules\MinRule;
use Vi\Validation\Rules\MultipleOfRule;
use Vi\Validation\Rules\NotInRule;
use Vi\Validation\Rules\NotRegexRule;
use Vi\Validation\Rules\NullableRule;
use Vi\Validation\Rules\NumericRule;
use Vi\Validation\Rules\PresentRule;
use Vi\Validation\Rules\ProhibitedIfRule;
use Vi\Validation\Rules\ProhibitedRule;
use Vi\Validation\Rules\ProhibitedUnlessRule;
use Vi\Validation\Rules\RegexRule;
use Vi\Validation\Rules\RequiredArrayKeysRule;
use Vi\Validation\Rules\RequiredIfRule;
use Vi\Validation\Rules\RequiredRule;
use Vi\Validation\Rules\RequiredUnlessRule;
use Vi\Validation\Rules\RequiredWithAllRule;
use Vi\Validation\Rules\RequiredWithoutAllRule;
use Vi\Validation\Rules\RequiredWithoutRule;
use Vi\Validation\Rules\RequiredWithRule;
use Vi\Validation\Rules\RuleInterface;
use Vi\Validation\Rules\SameRule;
use Vi\Validation\Rules\SizeRule;
use Vi\Validation\Rules\StartsWithRule;
use Vi\Validation\Rules\StringTypeRule;
use Vi\Validation\Rules\TimezoneRule;
use Vi\Validation\Rules\UlidRule;
use Vi\Validation\Rules\UppercaseRule;
use Vi\Validation\Rules\UrlRule;
use Vi\Validation\Rules\UuidRule;

final class LaravelRuleParser
{
    /**
     * @param string|array<int, string|Closure|RuleInterface> $definition
     * @return list<RuleInterface>
     */
    public function parse(string|array $definition): array
    {
        $rules = [];

        $parts = is_array($definition) ? $definition : explode('|', $definition);

        foreach ($parts as $part) {
            // Handle closure rules
            if ($part instanceof Closure) {
                $rules[] = new ClosureRule($part);
                continue;
            }

            // Handle RuleInterface instances directly
            if ($part instanceof RuleInterface) {
                $rules[] = $part;
                continue;
            }

            if ($part === '') {
                continue;
            }

            [$name, $params] = $this->splitRule($part);

            $rule = $this->mapRule($name, $params);

            if ($rule !== null) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private function splitRule(string $rule): array
    {
        $segments = explode(':', $rule, 2);
        $name = $segments[0];
        $params = isset($segments[1]) ? explode(',', $segments[1]) : [];

        return [$name, $params];
    }

    /**
     * @param list<string> $params
     */
    private function mapRule(string $name, array $params): ?RuleInterface
    {
        return match ($name) {
            // Core rules
            'required' => new RequiredRule(),
            'nullable' => new NullableRule(),
            'filled' => new FilledRule(),
            'present' => new PresentRule(),
            
            // Conditional required rules
            'required_if' => isset($params[0], $params[1]) ? new RequiredIfRule($params[0], array_slice($params, 1)) : null,
            'required_unless' => isset($params[0], $params[1]) ? new RequiredUnlessRule($params[0], array_slice($params, 1)) : null,
            'required_with' => !empty($params) ? new RequiredWithRule($params) : null,
            'required_without' => !empty($params) ? new RequiredWithoutRule($params) : null,
            'required_with_all' => !empty($params) ? new RequiredWithAllRule($params) : null,
            'required_without_all' => !empty($params) ? new RequiredWithoutAllRule($params) : null,
            
            // Type rules
            'string' => new StringTypeRule(),
            'integer', 'int' => new IntegerTypeRule(),
            'numeric' => new NumericRule(),
            'boolean', 'bool' => new BooleanRule(),
            'array' => new ArrayRule(),
            'date' => new DateRule($params[0] ?? null),
            'date_format' => isset($params[0]) ? new DateRule($params[0]) : null,
            'json' => new JsonRule(),
            
            // String rules
            'email' => new EmailRule(),
            'alpha' => new AlphaRule(),
            'alpha_num' => new AlphanumericRule(),
            'alpha_dash' => new AlphaDashRule(),
            'lowercase' => new LowercaseRule(),
            'uppercase' => new UppercaseRule(),
            'regex' => isset($params[0]) ? new RegexRule($params[0]) : null,
            'not_regex' => isset($params[0]) ? new NotRegexRule($params[0]) : null,
            'url' => new UrlRule(),
            'active_url' => new ActiveUrlRule(),
            'uuid' => new UuidRule(),
            'ulid' => new UlidRule(),
            'ip' => new IpRule(),
            'ipv4' => new IpRule('v4'),
            'ipv6' => new IpRule('v6'),
            'mac_address' => new MacAddressRule(),
            'timezone' => new TimezoneRule(),
            'starts_with' => !empty($params) ? new StartsWithRule($params) : null,
            'ends_with' => !empty($params) ? new EndsWithRule($params) : null,
            'doesnt_start_with' => !empty($params) ? new DoesntStartWithRule(...$params) : null,
            'doesnt_end_with' => !empty($params) ? new DoesntEndWithRule(...$params) : null,
            'digits' => isset($params[0]) ? new DigitsRule((int) $params[0]) : null,
            'digits_between' => isset($params[0], $params[1]) ? new DigitsBetweenRule((int) $params[0], (int) $params[1]) : null,
            
            // Size rules
            'min' => isset($params[0]) ? new MinRule((float) $params[0]) : null,
            'max' => isset($params[0]) ? new MaxRule((float) $params[0]) : null,
            'size' => isset($params[0]) ? new SizeRule((float) $params[0]) : null,
            'between' => isset($params[0], $params[1]) ? new BetweenRule((float) $params[0], (float) $params[1]) : null,
            'multiple_of' => isset($params[0]) ? new MultipleOfRule((float) $params[0]) : null,
            
            // Comparison rules
            'in' => new InRule($params),
            'not_in' => new NotInRule($params),
            'confirmed' => new ConfirmedRule(),
            'same' => isset($params[0]) ? new SameRule($params[0]) : null,
            'different' => isset($params[0]) ? new DifferentRule($params[0]) : null,
            'gt' => isset($params[0]) ? new GreaterThanRule($params[0]) : null,
            'gte' => isset($params[0]) ? new GreaterThanOrEqualRule($params[0]) : null,
            'lt' => isset($params[0]) ? new LessThanRule($params[0]) : null,
            'lte' => isset($params[0]) ? new LessThanOrEqualRule($params[0]) : null,
            
            // Date comparison rules
            'after' => isset($params[0]) ? new AfterRule($params[0]) : null,
            'after_or_equal' => isset($params[0]) ? new AfterOrEqualRule($params[0]) : null,
            'before' => isset($params[0]) ? new BeforeRule($params[0]) : null,
            'before_or_equal' => isset($params[0]) ? new BeforeOrEqualRule($params[0]) : null,
            'date_equals' => isset($params[0]) ? new DateEqualsRule($params[0]) : null,
            
            // Acceptance rules
            'accepted' => new AcceptedRule(),
            'declined' => new DeclinedRule(),
            
            // Prohibited rules
            'prohibited' => new ProhibitedRule(),
            'prohibited_if' => isset($params[0], $params[1]) ? new ProhibitedIfRule($params[0], array_slice($params, 1)) : null,
            'prohibited_unless' => isset($params[0], $params[1]) ? new ProhibitedUnlessRule($params[0], array_slice($params, 1)) : null,
            
            // Array rules
            'distinct' => new DistinctRule(
                in_array('strict', $params, true),
                in_array('ignore_case', $params, true)
            ),
            'required_array_keys' => !empty($params) ? new RequiredArrayKeysRule($params) : null,
            
            // File rules
            'file' => new FileRule(),
            'image' => new ImageRule(),
            'mimes', 'mimetypes' => new MimesRule($params),
            'max_file_size' => isset($params[0]) ? new MaxFileSizeRule((int) $params[0]) : null,
            
            default => null,
        };
    }
}
