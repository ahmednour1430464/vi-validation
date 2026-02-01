<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Execution\ErrorCollector;
use Vi\Validation\Execution\ValidationContext;
use Vi\Validation\Rules\ActiveUrlRule;
use Vi\Validation\Rules\AlphaDashRule;
use Vi\Validation\Rules\MacAddressRule;
use Vi\Validation\Rules\UlidRule;
use Vi\Validation\Rules\UppercaseRule;
use Vi\Validation\Rules\LowercaseRule;
use Vi\Validation\Rules\DateFormatRule;
use Vi\Validation\Rules\DateEqualsRule;
use Vi\Validation\Rules\MultipleOfRule;
use Vi\Validation\Rules\NotRegexRule;
use Vi\Validation\Rules\DoesntStartWithRule;
use Vi\Validation\Rules\DoesntEndWithRule;
use Vi\Validation\Rules\TimezoneRule;
use Vi\Validation\Rules\RequiredArrayKeysRule;
use Vi\Validation\Rules\ProhibitedIfRule;
use Vi\Validation\Rules\ProhibitedUnlessRule;

class MissingRulesTest extends TestCase
{
    private function createContext(array $data = []): ValidationContext
    {
        return new ValidationContext($data, new ErrorCollector());
    }

    public function testActiveUrlRule(): void
    {
        $rule = new ActiveUrlRule();
        // Skipping positive test to avoid network dependency in unit tests
        // $this->assertNull($rule->validate('https://google.com', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'active_url'], $rule->validate('invalid-url', 'field', $this->createContext()));
    }

    public function testAlphaDashRule(): void
    {
        $rule = new AlphaDashRule();
        $this->assertNull($rule->validate('foo-bar_123', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'alpha_dash'], $rule->validate('foo bar', 'field', $this->createContext()));
    }

    public function testMacAddressRule(): void
    {
        $rule = new MacAddressRule();
        $this->assertNull($rule->validate('00:0a:95:9d:68:16', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'mac_address'], $rule->validate('invalid-mac', 'field', $this->createContext()));
    }

    public function testUlidRule(): void
    {
        $rule = new UlidRule();
        $this->assertNull($rule->validate('01ARZ3NDEKTSV4RRFFQ69G5FAV', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'ulid'], $rule->validate('invalid-ulid', 'field', $this->createContext()));
    }

    public function testUppercaseRule(): void
    {
        $rule = new UppercaseRule();
        $this->assertNull($rule->validate('FOO', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'uppercase'], $rule->validate('Foo', 'field', $this->createContext()));
    }

    public function testLowercaseRule(): void
    {
        $rule = new LowercaseRule();
        $this->assertNull($rule->validate('foo', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'lowercase'], $rule->validate('Foo', 'field', $this->createContext()));
    }

    public function testDateFormatRule(): void
    {
        $rule = new DateFormatRule('Y-m-d');
        $this->assertNull($rule->validate('2023-10-01', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'date_format', 'parameters' => [0 => 'Y-m-d', 'format' => 'Y-m-d']], $rule->validate('01-10-2023', 'field', $this->createContext()));
    }

    public function testDateEqualsRule(): void
    {
        $rule = new DateEqualsRule('2023-10-01');
        $this->assertNull($rule->validate('2023-10-01', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'date_equals', 'parameters' => [0 => '2023-10-01', 'date' => '2023-10-01']], $rule->validate('2023-10-02', 'field', $this->createContext()));
    }

    public function testMultipleOfRule(): void
    {
        $rule = new MultipleOfRule(5);
        $this->assertNull($rule->validate(10, 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'multiple_of', 'parameters' => [0 => 5, 'value' => 5]], $rule->validate(12, 'field', $this->createContext()));
    }

    public function testNotRegexRule(): void
    {
        $rule = new NotRegexRule('/^foo/');
        $this->assertNull($rule->validate('bar', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'not_regex'], $rule->validate('foobar', 'field', $this->createContext()));
    }

    public function testDoesntStartWithRule(): void
    {
        $rule = new DoesntStartWithRule('foo');
        $this->assertNull($rule->validate('bar', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'doesnt_start_with', 'parameters' => [0 => 'foo', 'values' => 'foo']], $rule->validate('foobar', 'field', $this->createContext()));
    }

    public function testDoesntEndWithRule(): void
    {
        $rule = new DoesntEndWithRule('bar');
        $this->assertNull($rule->validate('foo', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'doesnt_end_with', 'parameters' => [0 => 'bar', 'values' => 'bar']], $rule->validate('foobar', 'field', $this->createContext()));
    }

    public function testTimezoneRule(): void
    {
        $rule = new TimezoneRule();
        $this->assertNull($rule->validate('UTC', 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'timezone'], $rule->validate('Invalid/Timezone', 'field', $this->createContext()));
    }

    public function testRequiredArrayKeysRule(): void
    {
        $rule = new RequiredArrayKeysRule('foo', 'bar');
        $this->assertNull($rule->validate(['foo' => 1, 'bar' => 2], 'field', $this->createContext()));
        $this->assertEquals(['rule' => 'required_array_keys', 'parameters' => ['foo', 'bar']], $rule->validate(['foo' => 1], 'field', $this->createContext()));
    }

    public function testProhibitedIfRule(): void
    {
        // If other=yes, then this field must be empty
        $rule = new ProhibitedIfRule('other', ['yes']);
        
        // Scenario 1: other=yes, field is not empty -> fail
        $context = $this->createContext(['other' => 'yes']);
        $this->assertEquals(['rule' => 'prohibited_if', 'parameters' => ['other' => 'other', 'value' => 'yes']], $rule->validate('value', 'field', $context));

        // Scenario 2: other=yes, field is empty -> pass
        $this->assertNull($rule->validate(null, 'field', $context));

        // Scenario 3: other=no, field is not empty -> pass
        $context = $this->createContext(['other' => 'no']);
        $this->assertNull($rule->validate('value', 'field', $context));
    }

    public function testProhibitedUnlessRule(): void
    {
        // Unless other=yes, then this field must be empty
        // -> If other!=yes, this field must be empty
        $rule = new ProhibitedUnlessRule('other', ['yes']);

        // Scenario 1: other=no, field is not empty -> fail
        $context = $this->createContext(['other' => 'no']);
        $this->assertEquals(['rule' => 'prohibited_unless', 'parameters' => ['other' => 'other', 'value' => 'yes']], $rule->validate('value', 'field', $context));

        // Scenario 2: other=no, field is empty -> pass
        $this->assertNull($rule->validate(null, 'field', $context));

        // Scenario 3: other=yes, field is not empty -> pass
        $context = $this->createContext(['other' => 'yes']);
        $this->assertNull($rule->validate('value', 'field', $context));
    }
}
