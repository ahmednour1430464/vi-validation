<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

interface NumericAwareInterface
{
    /**
     * Set whether the field under validation is numeric.
     */
    public function setNumeric(bool $numeric): void;
}
