<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Closure;
use Vi\Validation\Execution\ValidationContext;

/**
 * Wraps a closure to be used as a validation rule.
 * 
 * The closure should follow Laravel's convention:
 * function ($attribute, $value, $fail) {
 *     if ($value === 'invalid') {
 *         $fail('The :attribute is invalid.');
 *     }
 * }
 */
#[RuleName(RuleId::CLOSURE)]
final class ClosureRule implements RuleInterface
{
    private Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        $failMessage = null;

        $fail = function (string $message) use (&$failMessage): void {
            $failMessage = $message;
        };

        ($this->callback)($field, $value, $fail);

        if ($failMessage !== null) {
            return [
                'rule' => 'closure',
                'message' => $failMessage,
            ];
        }

        return null;
    }
}
