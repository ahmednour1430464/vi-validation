<?php

declare(strict_types=1);

namespace Vi\Validation;

use Vi\Validation\Schema\SchemaBuilder;

final class Validator
{
    public static function schema(): SchemaBuilder
    {
        return new SchemaBuilder();
    }
}
