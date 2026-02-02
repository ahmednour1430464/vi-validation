<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

/**
 * Interface for password checking logic.
 * Implement this to support rules like 'current_password'.
 */
interface PasswordHasherInterface
{
    /**
     * Check if the given password matches the current user's password.
     *
     * @param string $password
     * @return bool
     */
    public function check(string $password): bool;
}
