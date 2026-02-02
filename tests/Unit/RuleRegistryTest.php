<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Rules\RuleRegistry;
use Vi\Validation\Rules\RuleInterface;
use Vi\Validation\Rules\RuleName;
use Vi\Validation\Execution\ValidationContext;
use LogicException;

final class RuleRegistryTest extends TestCase
{
    private RuleRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RuleRegistry();
    }

    public function testDuplicateRuleNameThrowsException(): void
    {
        $this->registry->register(MockRuleA::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Rule name "mock_rule" is already registered by class "' . MockRuleA::class . '". Conflict with "' . MockRuleB::class . '".');

        $this->registry->register(MockRuleB::class);
    }

    public function testDuplicateAliasThrowsException(): void
    {
        $this->registry->register(MockRuleA::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Alias "alias_a" is already registered for rule "mock_rule" (' . MockRuleA::class . '). Conflict with rule "another_rule" (' . MockRuleWithDuplicateAlias::class . ').');

        $this->registry->register(MockRuleWithDuplicateAlias::class);
    }

    public function testReRegisteringSameClassIsAllowed(): void
    {
        $this->registry->register(MockRuleA::class);
        $this->registry->register(MockRuleA::class); // Should not throw

        $this->assertTrue($this->registry->has('mock_rule'));
        $this->assertEquals(MockRuleA::class, $this->registry->get('mock_rule'));
    }

    public function testAliasesAreCorrectlyRegistered(): void
    {
        $this->registry->register(MockRuleA::class);

        $this->assertTrue($this->registry->has('alias_a'));
        $this->assertTrue($this->registry->has('alias_b'));
        $this->assertEquals(MockRuleA::class, $this->registry->get('alias_a'));
        $this->assertEquals(MockRuleA::class, $this->registry->get('alias_b'));
    }
}

/**
 * Mock rules for testing
 */
#[RuleName('mock_rule', ['alias_a', 'alias_b'])]
class MockRuleA implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array { return null; }
}

#[RuleName('mock_rule')]
class MockRuleB implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array { return null; }
}

#[RuleName('another_rule', ['alias_a'])]
class MockRuleWithDuplicateAlias implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array { return null; }
}
