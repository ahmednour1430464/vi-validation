<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Rules\NullableRule;
use Vi\Validation\Rules\RuleInterface;

final class ValidatorEngine
{
    public function validate(CompiledSchema $schema, array $data): ValidationResult
    {
        $errors = new ErrorCollector();
        $context = new ValidationContext($data, $errors);

        foreach ($schema->getFields() as $field) {
            $value = $context->getValue($field->getName());

            $rules = $field->getRules();
            $isNullable = $this->isNullable($rules);

            if ($value === null && $isNullable) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($rule instanceof NullableRule) {
                    continue;
                }

                $this->applyRule($rule, $field->getName(), $value, $context);
            }
        }

        return new ValidationResult($errors);
    }

    /**
     * @param list<RuleInterface> $rules
     */
    private function isNullable(array $rules): bool
    {
        foreach ($rules as $rule) {
            if ($rule instanceof NullableRule) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param RuleInterface $rule
     */
    private function applyRule(RuleInterface $rule, string $field, mixed $value, ValidationContext $context): void
    {
        $error = $rule->validate($value, $field, $context);

        if ($error !== null) {
            $context->addError($field, $error['rule'], $error['message'] ?? null);
        }
    }
}
