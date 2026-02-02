<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Rules\RuleId;
use Vi\Validation\Rules\RuleRegistry;
use Vi\Validation\Rules\RuleName;
use ReflectionClass;

class RuleIdTest extends TestCase
{
    public function test_all_enum_cases_match_registered_rules(): void
    {
        $registry = new RuleRegistry();
        $registry->registerBuiltInRules();

        $reflection = new ReflectionClass(RuleRegistry::class);
        $method = $reflection->getMethod('registerBuiltInRules');
        
        // This is a bit complex to verify perfectly without duplicating the list, 
        // but we can check that every RuleId value exists in the registry.
        
        foreach (RuleId::cases() as $case) {
            $this->assertTrue(
                $registry->has($case->value),
                sprintf('RuleId case "%s" (value: "%s") is not registered in RuleRegistry.', $case->name, $case->value)
            );
        }
    }

    public function test_all_registered_rules_have_enum_case(): void
    {
        $registry = new RuleRegistry();
        $registry->registerBuiltInRules();

        // Get the private 'rules' array from registry
        $reflection = new ReflectionClass($registry);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);
        $rules = $property->getValue($registry);

        $enumValues = array_map(fn($case) => $case->value, RuleId::cases());

        foreach (array_keys($rules) as $ruleName) {
            // Skip 'closure' if it's not in the enum (but I added it)
            if ($ruleName === 'closure' && !in_array('closure', $enumValues)) {
                continue;
            }

            $this->assertContains(
                $ruleName,
                $enumValues,
                sprintf('Registered rule "%s" does not have a corresponding case in RuleId enum.', $ruleName)
            );
        }
    }

    public function test_registry_supports_rule_id_enum(): void
    {
        $registry = new RuleRegistry();
        $registry->registerBuiltInRules();

        $this->assertTrue($registry->has(RuleId::REQUIRED));
        $this->assertEquals(
            $registry->get('required'),
            $registry->get(RuleId::REQUIRED)
        );
    }
}
