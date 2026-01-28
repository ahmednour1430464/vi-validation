<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Messages\MessageResolver;
use Vi\Validation\Rules\NullableRule;
use Vi\Validation\Rules\RuleInterface;

final class ValidatorEngine
{
    private ?MessageResolver $messageResolver;
    private bool $failFast;
    private int $maxErrors;

    public function __construct(
        ?MessageResolver $messageResolver = null,
        bool $failFast = false,
        int $maxErrors = 100
    ) {
        $this->messageResolver = $messageResolver ?? new MessageResolver();
        $this->failFast = $failFast;
        $this->maxErrors = $maxErrors;
    }

    public function validate(CompiledSchema $schema, array $data): ValidationResult
    {
        $errors = new ErrorCollector();
        $context = new ValidationContext($data, $errors);

        foreach ($schema->getFields() as $field) {
            if ($this->shouldStopValidation($errors)) {
                break;
            }

            $value = $context->getValue($field->getName());

            $rules = $field->getRules();
            $isNullable = $this->isNullable($rules);

            if ($value === null && $isNullable) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($this->shouldStopValidation($errors)) {
                    break;
                }

                if ($rule instanceof NullableRule) {
                    continue;
                }

                $this->applyRule($rule, $field->getName(), $value, $context);
            }
        }

        return new ValidationResult($errors, $data, $this->messageResolver);
    }

    public function setFailFast(bool $failFast): void
    {
        $this->failFast = $failFast;
    }

    public function setMaxErrors(int $maxErrors): void
    {
        $this->maxErrors = $maxErrors;
    }

    public function setMessageResolver(MessageResolver $resolver): void
    {
        $this->messageResolver = $resolver;
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

    private function shouldStopValidation(ErrorCollector $errors): bool
    {
        if ($this->failFast && $errors->hasErrors()) {
            return true;
        }

        if ($errors->count() >= $this->maxErrors) {
            return true;
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
            $message = $error['message'] ?? null;
            
            // Resolve message if MessageResolver is available and no custom message was provided
            if ($message === null && $this->messageResolver !== null) {
                $params = $error['params'] ?? [];
                $message = $this->messageResolver->resolve($field, $error['rule'], $params);
            }

            $context->addError($field, $error['rule'], $message);
        }
    }
}
