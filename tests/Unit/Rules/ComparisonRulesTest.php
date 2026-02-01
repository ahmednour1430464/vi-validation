<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Execution\ErrorCollector;
use Vi\Validation\Execution\ValidationContext;
use Vi\Validation\Rules\InRule;
use Vi\Validation\Rules\NotInRule;
use Vi\Validation\Rules\BetweenRule;
use Vi\Validation\Rules\SizeRule;
use Vi\Validation\Rules\ConfirmedRule;
use Vi\Validation\Rules\SameRule;
use Vi\Validation\Rules\DifferentRule;

class ComparisonRulesTest extends TestCase
{
    private function createContext(array $data = []): ValidationContext
    {
        return new ValidationContext($data, new ErrorCollector());
    }

    // InRule Tests
    public function testInRulePassesWhenValueInList(): void
    {
        $rule = new InRule(['apple', 'banana', 'orange']);
        $this->assertNull($rule->validate('apple', 'field', $this->createContext()));
    }

    public function testInRuleFailsWhenValueNotInList(): void
    {
        $rule = new InRule(['apple', 'banana', 'orange']);
        $result = $rule->validate('grape', 'field', $this->createContext());
        $this->assertEquals(['rule' => 'in'], $result);
    }

    // NotInRule Tests
    public function testNotInRulePassesWhenValueNotInList(): void
    {
        $rule = new NotInRule(['banned', 'restricted']);
        $this->assertNull($rule->validate('allowed', 'field', $this->createContext()));
    }

    public function testNotInRuleFailsWhenValueInList(): void
    {
        $rule = new NotInRule(['banned', 'restricted']);
        $result = $rule->validate('banned', 'field', $this->createContext());
        $this->assertEquals(['rule' => 'not_in'], $result);
    }

    // BetweenRule Tests
    public function testBetweenRulePassesWithNumberInRange(): void
    {
        $rule = new BetweenRule(1, 10);
        $this->assertNull($rule->validate(5, 'field', $this->createContext()));
    }

    public function testBetweenRulePassesWithStringLengthInRange(): void
    {
        $rule = new BetweenRule(3, 10);
        $this->assertNull($rule->validate('hello', 'field', $this->createContext()));
    }

    public function testBetweenRulePassesWithArrayCountInRange(): void
    {
        $rule = new BetweenRule(1, 5);
        $this->assertNull($rule->validate(['a', 'b', 'c'], 'field', $this->createContext()));
    }

    public function testBetweenRuleFailsWhenBelowRange(): void
    {
        $rule = new BetweenRule(10, 100);
        $result = $rule->validate(5, 'field', $this->createContext());
        $this->assertEquals('between', $result['rule']);
    }

    public function testBetweenRuleFailsWhenAboveRange(): void
    {
        $rule = new BetweenRule(10, 100);
        $result = $rule->validate(150, 'field', $this->createContext());
        $this->assertEquals('between', $result['rule']);
    }

    // SizeRule Tests
    public function testSizeRulePassesWithExactNumber(): void
    {
        $rule = new SizeRule(10);
        $this->assertNull($rule->validate(10, 'field', $this->createContext()));
    }

    public function testSizeRulePassesWithExactStringLength(): void
    {
        $rule = new SizeRule(5);
        $this->assertNull($rule->validate('hello', 'field', $this->createContext()));
    }

    public function testSizeRulePassesWithExactArrayCount(): void
    {
        $rule = new SizeRule(3);
        $this->assertNull($rule->validate(['a', 'b', 'c'], 'field', $this->createContext()));
    }

    public function testSizeRuleFailsWithDifferentSize(): void
    {
        $rule = new SizeRule(5);
        $result = $rule->validate('hi', 'field', $this->createContext());
        $this->assertEquals('size', $result['rule']);
    }

    // ConfirmedRule Tests
    public function testConfirmedRulePassesWhenFieldsMatch(): void
    {
        $rule = new ConfirmedRule();
        $context = $this->createContext([
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ]);
        $this->assertNull($rule->validate('secret123', 'password', $context));
    }

    public function testConfirmedRuleFailsWhenFieldsDontMatch(): void
    {
        $rule = new ConfirmedRule();
        $context = $this->createContext([
            'password' => 'secret123',
            'password_confirmation' => 'different'
        ]);
        $result = $rule->validate('secret123', 'password', $context);
        $this->assertEquals(['rule' => 'confirmed'], $result);
    }

    // SameRule Tests
    public function testSameRulePassesWhenFieldsMatch(): void
    {
        $rule = new SameRule('other_field');
        $context = $this->createContext([
            'field' => 'value',
            'other_field' => 'value'
        ]);
        $this->assertNull($rule->validate('value', 'field', $context));
    }

    public function testSameRuleFailsWhenFieldsDontMatch(): void
    {
        $rule = new SameRule('other_field');
        $context = $this->createContext([
            'field' => 'value1',
            'other_field' => 'value2'
        ]);
        $result = $rule->validate('value1', 'field', $context);
        $this->assertEquals(['rule' => 'same'], $result);
    }

    // DifferentRule Tests
    public function testDifferentRulePassesWhenFieldsDiffer(): void
    {
        $rule = new DifferentRule('other_field');
        $context = $this->createContext([
            'field' => 'value1',
            'other_field' => 'value2'
        ]);
        $this->assertNull($rule->validate('value1', 'field', $context));
    }

    public function testDifferentRuleFailsWhenFieldsMatch(): void
    {
        $rule = new DifferentRule('other_field');
        $context = $this->createContext([
            'field' => 'same',
            'other_field' => 'same'
        ]);
        $result = $rule->validate('same', 'field', $context);
        $this->assertEquals(['rule' => 'different'], $result);
    }
}
