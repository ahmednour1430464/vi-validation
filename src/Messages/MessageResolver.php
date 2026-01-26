<?php

declare(strict_types=1);

namespace Vi\Validation\Messages;

final class MessageResolver
{
    private TranslatorInterface $translator;

    /** @var array<string, string> */
    private array $customMessages = [];

    /** @var array<string, string> */
    private array $customAttributes = [];

    public function __construct(?TranslatorInterface $translator = null)
    {
        $this->translator = $translator ?? new Translator();
    }

    /**
     * Resolve the error message for a validation rule.
     *
     * @param array<string, mixed> $params
     */
    public function resolve(string $field, string $rule, array $params = []): string
    {
        // Check for custom message first (field.rule format)
        $customKey = $field . '.' . $rule;
        if (isset($this->customMessages[$customKey])) {
            return $this->replaceAttributes(
                $this->customMessages[$customKey],
                $field,
                $params
            );
        }

        // Check for rule-level custom message
        if (isset($this->customMessages[$rule])) {
            return $this->replaceAttributes(
                $this->customMessages[$rule],
                $field,
                $params
            );
        }

        // Get from translator
        $message = $this->getTranslatedMessage($rule, $params);

        return $this->replaceAttributes($message, $field, $params);
    }

    /**
     * @param array<string, string> $messages
     */
    public function setCustomMessages(array $messages): void
    {
        $this->customMessages = $messages;
    }

    /**
     * @param array<string, string> $attributes
     */
    public function setCustomAttributes(array $attributes): void
    {
        $this->customAttributes = $attributes;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getTranslatedMessage(string $rule, array $params): string
    {
        // Handle rules with type-specific messages (min, max, size, between)
        $typeSpecificRules = ['min', 'max', 'size', 'between'];

        if (in_array($rule, $typeSpecificRules, true) && isset($params['type'])) {
            $type = $params['type'];
            $key = $rule . '.' . $type;
            $message = $this->translator->get($key);

            // If we got back the key itself, fall back to default
            if ($message !== $key) {
                return $message;
            }
        }

        return $this->translator->get($rule);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function replaceAttributes(string $message, string $field, array $params): string
    {
        $attribute = $this->customAttributes[$field] ?? $this->formatAttribute($field);

        $replacements = array_merge($params, ['attribute' => $attribute]);

        foreach ($replacements as $key => $value) {
            if (is_scalar($value)) {
                $message = str_replace(
                    [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                    [(string) $value, strtoupper((string) $value), ucfirst((string) $value)],
                    $message
                );
            }
        }

        return $message;
    }

    private function formatAttribute(string $field): string
    {
        return str_replace(['_', '-'], ' ', $field);
    }
}
