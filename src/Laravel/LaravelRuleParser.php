<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Vi\Validation\Rules\EmailRule;
use Vi\Validation\Rules\IntegerTypeRule;
use Vi\Validation\Rules\MaxRule;
use Vi\Validation\Rules\MinRule;
use Vi\Validation\Rules\RequiredRule;
use Vi\Validation\Rules\RuleInterface;
use Vi\Validation\Rules\StringTypeRule;

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
            'required' => new RequiredRule(),
            'string' => new StringTypeRule(),
            'integer' => new IntegerTypeRule(),
            'email' => new EmailRule(),
            'min' => isset($params[0]) ? new MinRule((float) $params[0]) : null,
            'max' => isset($params[0]) ? new MaxRule((float) $params[0]) : null,
            default => null,
        };
    }
}
