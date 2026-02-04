<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class RuleName
{
    /**
     * @param string|RuleId $name
     * @param string[] $aliases
     */
    public function __construct(
        public readonly string|RuleId $name,
        public readonly array $aliases = []
    ) {
    }
}
