<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function testBasicValidationPasses(): void
    {
        $schema = Validator::schema()
            ->field('name')->required()->string()->max(100)
            ->field('email')->required()->email()
            ->field('age')->required()->integer()->min(18)
            ->compile();

        $result = $schema->validate([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        $this->assertTrue($result->isValid());
    }

    public function testBasicValidationFails(): void
    {
        $schema = Validator::schema()
            ->field('name')->required()->string()->max(100)
            ->field('email')->required()->email()
            ->field('age')->required()->integer()->min(18)
            ->compile();

        $result = $schema->validate([
            'name' => '',
            'email' => 'invalid-email',
            'age' => 15
        ]);

        $this->assertFalse($result->isValid());
        
        $errors = $result->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);
    }

    public function testNestedFieldValidation(): void
    {
        $schema = Validator::schema()
            ->field('user.name')->required()->string()
            ->field('user.email')->required()->email()
            ->compile();

        $result = $schema->validate([
            'user' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com'
            ]
        ]);

        $this->assertTrue($result->isValid());
    }

    public function testNestedFieldValidationFails(): void
    {
        $schema = Validator::schema()
            ->field('user.name')->required()->string()
            ->field('user.email')->required()->email()
            ->compile();

        $result = $schema->validate([
            'user' => [
                'name' => '',
                'email' => 'invalid'
            ]
        ]);

        $this->assertFalse($result->isValid());
        
        $errors = $result->errors();
        $this->assertArrayHasKey('user.name', $errors);
        $this->assertArrayHasKey('user.email', $errors);
    }

    public function testNullableField(): void
    {
        $schema = Validator::schema()
            ->field('optional_field')->nullable()->string()
            ->compile();

        $result = $schema->validate([
            'optional_field' => null
        ]);

        $this->assertTrue($result->isValid());
    }

    public function testBatchValidation(): void
    {
        $schema = Validator::schema()
            ->field('id')->required()->integer()
            ->field('name')->required()->string()
            ->compile();

        $validator = new \Vi\Validation\SchemaValidator($schema);
        
        $results = $validator->validateMany([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie']
        ]);

        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertTrue($result->isValid());
        }
    }
}
