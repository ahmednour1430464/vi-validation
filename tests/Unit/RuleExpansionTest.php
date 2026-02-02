<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Validator;

class RuleExpansionTest extends TestCase
{
    public function testPhaseANumericComparisons(): void
    {
        $schema = Validator::schema()
            ->field('price')->numeric()->gt('other_price')
            ->field('score')->numeric()->gte('min_score')
            ->field('age')->numeric()->lt('max_age')
            ->field('weight')->numeric()->lte('limit')
            ->compile();

        $result = $schema->validate([
            'price' => 10, 'other_price' => 5,
            'score' => 10, 'min_score' => 10,
            'age' => 20, 'max_age' => 25,
            'weight' => 50, 'limit' => 50,
        ]);
        $this->assertTrue($result->isValid(), 'Should pass comparison');

        $result = $schema->validate([
            'price' => 5, 'other_price' => 10,
            'score' => 9, 'min_score' => 10,
            'age' => 25, 'max_age' => 20,
            'weight' => 51, 'limit' => 50,
        ]);
        $this->assertFalse($result->isValid(), 'Should fail comparison');
    }

    public function testPhaseAPresenceLogic(): void
    {
        // Test requiredWith
        $schema = Validator::schema()
            ->field('password')->requiredWith('username')
            ->compile();
        
        $result = $schema->validate(['username' => 'john']);
        $this->assertFalse($result->isValid(), 'Password should be required with username');
        
        $result = $schema->validate(['username' => 'john', 'password' => 'secret']);
        $this->assertTrue($result->isValid());

        // Test requiredUnless
        $schema = Validator::schema()
            ->field('email')->requiredUnless('type', ['guest'])
            ->compile();
            
        $result = $schema->validate(['type' => 'user']);
        $this->assertFalse($result->isValid(), 'Email should be required unless type is guest');
        
        $result = $schema->validate(['type' => 'guest']);
        $this->assertTrue($result->isValid(), 'Email should not be required if type is guest');

        // Test present
        $schema = Validator::schema()
            ->field('cv')->present()
            ->compile();
            
        $result = $schema->validate(['cv' => null]);
        $this->assertTrue($result->isValid(), 'cv should be present (even if null)');
        
        $result = $schema->validate([]);
        $this->assertFalse($result->isValid(), 'cv should be missing');

        // Test filled
        $schema = Validator::schema()
            ->field('bio')->filled()
            ->compile();
            
        $result = $schema->validate(['bio' => '']);
        $this->assertFalse($result->isValid(), 'bio should be filled');
        
        $result = $schema->validate(['bio' => 'hello']);
        $this->assertTrue($result->isValid());
    }

    public function testPhaseBAliasesAndCommonRules(): void
    {
        $schema = Validator::schema()
            ->field('name')->str()->startsWith('Mr.', 'Ms.')
            ->field('code')->int()->digits(4)
            ->field('tags')->array()->distinct()
            ->compile();

        $result = $schema->validate([
            'name' => 'Mr. Smith',
            'code' => 1234,
            'tags' => ['a', 'b', 'c']
        ]);
        $this->assertTrue($result->isValid());

        $result = $schema->validate([
            'name' => 'John',
            'code' => 123,
            'tags' => ['a', 'a']
        ]);
        $this->assertFalse($result->isValid());
    }

    public function testWhenConditionalLogic(): void
    {
        $schema = Validator::schema()
            ->field('type')->string()
            ->field('amount')->when(
                fn($data) => ($data['type'] ?? '') === 'bonus',
                fn($f) => $f->required()->numeric()->min(100),
                fn($f) => $f->nullable()->numeric()->max(10)
            )
            ->compile();

        // Bonus path: must be >= 100
        $result = $schema->validate(['type' => 'bonus', 'amount' => 150]);
        $this->assertTrue($result->isValid());
        $result = $schema->validate(['type' => 'bonus', 'amount' => 50]);
        $this->assertFalse($result->isValid());

        // Normal path: must be <= 10
        $result = $schema->validate(['type' => 'normal', 'amount' => 5]);
        $this->assertTrue($result->isValid());
        $result = $schema->validate(['type' => 'normal', 'amount' => 15]);
        $this->assertFalse($result->isValid());
    }

    public function testPhaseCOptimizationReordering(): void
    {
        // We want to verify that Bail and Required are checked first
        // Field definition order: min, required, bail
        $schema = Validator::schema()
            ->field('test')->min(10)->required()->bail()
            ->compile();

        $result = $schema->validate([]);
        $this->assertFalse($result->isValid());
        
        // Error should be 'required', not 'min' if reordered correctly
        $messages = $result->messages()['test'];
        $this->assertEquals('The test field is required.', $messages[0]);
    }
}
