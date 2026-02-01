<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Closure;
use Vi\Validation\Rules\ClosureRule;
use Vi\Validation\Rules\RuleInterface;
use Vi\Validation\Rules\RuleRegistry;

final class LaravelRuleParser
{
    private RuleRegistry $registry;

    public function __construct(?RuleRegistry $registry = null)
    {
        $this->registry = $registry ?? new RuleRegistry();
        if ($registry === null) {
            $this->registry->registerBuiltInRules();
        }
    }

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
        $class = $this->registry->get($name);
        if ($class === null) {
            return null;
        }

        return match ($name) {
            // Conditional required rules
            'required_if', 'prohibited_if', 'prohibited_unless' => isset($params[0], $params[1]) ? new $class($params[0], array_slice($params, 1)) : null,
            'required_unless' => isset($params[0], $params[1]) ? new $class($params[0], array_slice($params, 1)) : null,
            
            'required_with', 'required_without', 'required_with_all', 'required_without_all', 'required_array_keys', 'in', 'not_in', 'starts_with', 'ends_with', 'mimes', 'mimetypes' => !empty($params) || in_array($name, ['in', 'not_in', 'mimes', 'mimetypes']) ? new $class($params) : null,
            
            // Type rules with params
            'date' => new $class($params[0] ?? null),
            'date_format' => isset($params[0]) ? new $class($params[0]) : null,
            
            // String rules with single param
            'regex', 'not_regex' => isset($params[0]) ? new $class($params[0]) : null,
            'digits', 'max_file_size' => isset($params[0]) ? new $class((int) $params[0]) : null,
            'min', 'max', 'size', 'multiple_of' => isset($params[0]) ? new $class((float) $params[0]) : null,
            
            // Rules with multiple params
            'digits_between', 'between' => isset($params[0], $params[1]) ? new $class((float) $params[0], (float) $params[1]) : null,
            
            'doesnt_start_with', 'doesnt_end_with' => !empty($params) ? new $class(...$params) : null,
            
            'same', 'different', 'gt', 'gte', 'lt', 'lte', 'after', 'after_or_equal', 'before', 'before_or_equal', 'date_equals' => isset($params[0]) ? new $class($params[0]) : null,
            
            'distinct' => new $class(
                in_array('strict', $params, true),
                in_array('ignore_case', $params, true)
            ),
            
            // Default: Simple instantiation for rules without params
            default => new $class(),
        };
    }
}
