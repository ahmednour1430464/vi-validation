<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Vi\Validation\Rules\AlphanumericRule;
use Vi\Validation\Rules\AlphaRule;
use Vi\Validation\Rules\ArrayRule;
use Vi\Validation\Rules\BetweenRule;
use Vi\Validation\Rules\BooleanRule;
use Vi\Validation\Rules\ConfirmedRule;
use Vi\Validation\Rules\DateRule;
use Vi\Validation\Rules\DifferentRule;
use Vi\Validation\Rules\EmailRule;
use Vi\Validation\Rules\FileRule;
use Vi\Validation\Rules\ImageRule;
use Vi\Validation\Rules\InRule;
use Vi\Validation\Rules\IntegerTypeRule;
use Vi\Validation\Rules\IpRule;
use Vi\Validation\Rules\JsonRule;
use Vi\Validation\Rules\MaxFileSizeRule;
use Vi\Validation\Rules\MaxRule;
use Vi\Validation\Rules\MimesRule;
use Vi\Validation\Rules\MinRule;
use Vi\Validation\Rules\NotInRule;
use Vi\Validation\Rules\NullableRule;
use Vi\Validation\Rules\NumericRule;
use Vi\Validation\Rules\RegexRule;
use Vi\Validation\Rules\RequiredRule;
use Vi\Validation\Rules\RuleInterface;
use Vi\Validation\Rules\SameRule;
use Vi\Validation\Rules\SizeRule;
use Vi\Validation\Rules\StringTypeRule;
use Vi\Validation\Rules\UrlRule;
use Vi\Validation\Rules\UuidRule;

final class LaravelRuleParser
{
    /**
     * @param string|array<int, string> $definition
     * @return list<RuleInterface>
     */
    public function parse(string|array $definition): array
    {
        $rules = [];

        $parts = is_array($definition) ? $definition : explode('|', $definition);

        foreach ($parts as $part) {
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
            'regex' => isset($params[0]) ? new RegexRule($params[0]) : null,
            'url' => new UrlRule(),
            'uuid' => new UuidRule(),
            'ip' => new IpRule(),
            'ipv4' => new IpRule('v4'),
            'ipv6' => new IpRule('v6'),
            
            // Size rules
            'min' => isset($params[0]) ? new MinRule((float) $params[0]) : null,
            'max' => isset($params[0]) ? new MaxRule((float) $params[0]) : null,
            'size' => isset($params[0]) ? new SizeRule((float) $params[0]) : null,
            'between' => isset($params[0], $params[1]) ? new BetweenRule((float) $params[0], (float) $params[1]) : null,
            
            // Comparison rules
            'in' => new InRule($params),
            'not_in' => new NotInRule($params),
            'confirmed' => new ConfirmedRule(),
            'same' => isset($params[0]) ? new SameRule($params[0]) : null,
            'different' => isset($params[0]) ? new DifferentRule($params[0]) : null,
            
            // File rules
            'file' => new FileRule(),
            'image' => new ImageRule(),
            'mimes', 'mimetypes' => new MimesRule($params),
            'max_file_size' => isset($params[0]) ? new MaxFileSizeRule((int) $params[0]) : null,
            
            default => null,
        };
    }
}
