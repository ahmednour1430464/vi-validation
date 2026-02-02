<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::UNIQUE)]
final class UniqueRule implements RuleInterface
{
    private string $table;
    private string $column;
    private mixed $ignoreId;
    private string $idColumn;
    private array $extraConstraints;

    public function __construct(string $table, string $column = 'id', mixed $ignoreId = null, string $idColumn = 'id', array $extraConstraints = [])
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->idColumn = $idColumn;
        $this->extraConstraints = $extraConstraints;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        $dbValidator = $context->getDatabaseValidator();

        if ($dbValidator === null) {
            return null;
        }

        if (!$dbValidator->unique($this->table, $this->column, $value, $this->ignoreId, $this->idColumn, $this->extraConstraints)) {
            return ['rule' => 'unique'];
        }

        return null;
    }
}
