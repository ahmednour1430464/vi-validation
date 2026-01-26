<?php

declare(strict_types=1);

namespace Vi\Validation\Messages;

interface TranslatorInterface
{
    /**
     * Get the translation for a given key.
     *
     * @param array<string, string> $replace
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string;

    /**
     * Get the current locale.
     */
    public function getLocale(): string;

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale): void;
}
