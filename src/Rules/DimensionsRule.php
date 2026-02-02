<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::DIMENSIONS)]
final class DimensionsRule implements RuleInterface
{
    /** @var array<string, int> */
    private array $constraints;

    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        $path = $this->getPath($value);
        if ($path === null || !file_exists($path)) {
            return ['rule' => 'dimensions'];
        }

        $size = @getimagesize($path);
        if ($size === false) {
            return ['rule' => 'dimensions'];
        }

        [$width, $height] = $size;

        if (isset($this->constraints['width']) && $width !== (int) $this->constraints['width']) {
            return ['rule' => 'dimensions'];
        }

        if (isset($this->constraints['height']) && $height !== (int) $this->constraints['height']) {
            return ['rule' => 'dimensions'];
        }

        if (isset($this->constraints['min_width']) && $width < (int) $this->constraints['min_width']) {
            return ['rule' => 'dimensions'];
        }

        if (isset($this->constraints['min_height']) && $height < (int) $this->constraints['min_height']) {
            return ['rule' => 'dimensions'];
        }

        if (isset($this->constraints['max_width']) && $width > (int) $this->constraints['max_width']) {
            return ['rule' => 'dimensions'];
        }

        if (isset($this->constraints['max_height']) && $height > (int) $this->constraints['max_height']) {
            return ['rule' => 'dimensions'];
        }

        if (isset($this->constraints['ratio'])) {
            [$targetWidth, $targetHeight] = explode('/', (string) $this->constraints['ratio']);
            if (abs(($width / $height) - ($targetWidth / $targetHeight)) > 0.01) {
                return ['rule' => 'dimensions'];
            }
        }

        return null;
    }

    private function getPath(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof \SplFileInfo) {
            return $value->getPathname();
        }

        return null;
    }
}
