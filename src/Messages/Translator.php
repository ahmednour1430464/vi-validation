<?php

declare(strict_types=1);

namespace Vi\Validation\Messages;

final class Translator implements TranslatorInterface
{
    private string $locale;

    /** @var array<string, array<string, string|array<string, string>>> */
    private array $messages = [];

    /** @var array<string, array<string, string|array<string, string>>> */
    private array $fallbackMessages = [];

    private string $fallbackLocale = 'en';

    public function __construct(string $locale = 'en')
    {
        $this->locale = $locale;
        $this->loadDefaultMessages();
    }

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        $message = $this->getMessage($key, $locale);

        if ($message === null) {
            $message = $this->getMessage($key, $this->fallbackLocale);
        }

        if ($message === null) {
            return $key;
        }

        return $this->replaceAttributes($message, $replace);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @param array<string, string|array<string, string>> $messages
     */
    public function addMessages(array $messages, string $locale): void
    {
        if (!isset($this->messages[$locale])) {
            $this->messages[$locale] = [];
        }

        $this->messages[$locale] = array_merge($this->messages[$locale], $messages);
    }

    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    private function getMessage(string $key, string $locale): ?string
    {
        $messages = $this->messages[$locale] ?? [];

        // Handle nested keys like 'min.string'
        $parts = explode('.', $key);

        if (count($parts) === 1) {
            $message = $messages[$key] ?? null;
            return is_string($message) ? $message : null;
        }

        $current = $messages;
        foreach ($parts as $part) {
            if (!is_array($current) || !isset($current[$part])) {
                return null;
            }
            $current = $current[$part];
        }

        return is_string($current) ? $current : null;
    }

    /**
     * @param array<string, string> $replace
     */
    private function replaceAttributes(string $message, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $message = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $message
            );
        }

        return $message;
    }

    private function loadDefaultMessages(): void
    {
        $this->messages['en'] = [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'integer' => 'The :attribute must be an integer.',
            'numeric' => 'The :attribute must be a number.',
            'boolean' => 'The :attribute field must be true or false.',
            'array' => 'The :attribute must be an array.',
            'date' => 'The :attribute is not a valid date.',
            'json' => 'The :attribute must be a valid JSON string.',
            'email' => 'The :attribute must be a valid email address.',
            'alpha' => 'The :attribute must only contain letters.',
            'alpha_num' => 'The :attribute must only contain letters and numbers.',
            'regex' => 'The :attribute format is invalid.',
            'url' => 'The :attribute must be a valid URL.',
            'uuid' => 'The :attribute must be a valid UUID.',
            'ip' => 'The :attribute must be a valid IP address.',
            'in' => 'The selected :attribute is invalid.',
            'not_in' => 'The selected :attribute is invalid.',
            'between' => [
                'numeric' => 'The :attribute must be between :min and :max.',
                'string' => 'The :attribute must be between :min and :max characters.',
                'array' => 'The :attribute must have between :min and :max items.',
            ],
            'size' => [
                'numeric' => 'The :attribute must be :size.',
                'string' => 'The :attribute must be :size characters.',
                'array' => 'The :attribute must contain :size items.',
            ],
            'min' => [
                'numeric' => 'The :attribute must be at least :min.',
                'string' => 'The :attribute must be at least :min characters.',
                'array' => 'The :attribute must have at least :min items.',
            ],
            'max' => [
                'numeric' => 'The :attribute must not be greater than :max.',
                'string' => 'The :attribute must not be greater than :max characters.',
                'array' => 'The :attribute must not have more than :max items.',
            ],
            'confirmed' => 'The :attribute confirmation does not match.',
            'same' => 'The :attribute and :other must match.',
            'different' => 'The :attribute and :other must be different.',
            'file' => 'The :attribute must be a file.',
            'image' => 'The :attribute must be an image.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'max_file_size' => 'The :attribute must not be greater than :max kilobytes.',
        ];
    }
}
