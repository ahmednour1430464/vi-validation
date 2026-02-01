<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class RuleName
{
    public function __construct(
        public readonly string $name,
        public readonly array $aliases = []
    ) {
    }
}
