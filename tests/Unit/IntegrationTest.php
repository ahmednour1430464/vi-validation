<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Validator;

class IntegrationTest extends TestCase
{
    public function testFluentInterfaceAndErrorMessages(): void
    {
        $schema = Validator::schema()
            ->field('url')->activeUrl()
            ->field('username')->alphaDash()
            ->field('mac')->macAddress()
            ->field('uuid')->ulid() // purposefully misnamed field to match rule for testing
            ->field('code')->uppercase()
            ->field('event_date')->dateFormat('Y-m-d')
            ->field('score')->multipleOf(5)
            ->field('comment')->notRegex('/bad/')
            ->compile();

        $result = $schema->validate([
            'url' => 'not-a-url',
            'username' => 'invalid user name', // space not allowed
            'mac' => 'invalid-mac',
            'uuid' => 'invalid-ulid',
            'code' => 'lowercase',
            'event_date' => '01-01-2023', // wrong format
            'score' => 7,
            'comment' => 'this is bad',
        ]);

        $this->assertFalse($result->isValid());
        $errors = $result->errors();

        // Check error keys exist
        $this->assertArrayHasKey('url', $errors);
        $this->assertArrayHasKey('username', $errors);
        $this->assertArrayHasKey('mac', $errors);
        $this->assertArrayHasKey('uuid', $errors);
        $this->assertArrayHasKey('code', $errors);
        $this->assertArrayHasKey('event_date', $errors);
        $this->assertArrayHasKey('score', $errors);
        $this->assertArrayHasKey('comment', $errors);

        // Check error messages (English default)
        var_dump($errors); 
        $this->assertEquals(['The url is not a valid URL.'], $errors['url']);
        $this->assertEquals(['The username must only contain letters, numbers, dashes and underscores.'], $errors['username']);
        $this->assertEquals(['The mac must be a valid MAC address.'], $errors['mac']);
        $this->assertEquals(['The uuid must be a valid ULID.'], $errors['uuid']);
        $this->assertEquals(['The code must be uppercase.'], $errors['code']);
        $this->assertEquals(['The event_date does not match the format Y-m-d.'], $errors['event_date']);
        $this->assertEquals(['The score must be a multiple of 5.'], $errors['score']);
        $this->assertEquals(['The comment format is invalid.'], $errors['comment']);
    }

    public function testProhibitedRulesIntegration(): void
    {
        $schema = Validator::schema()
            ->field('secret')->prohibitedIf('public', [true])
            ->compile();

        // Should fail
        $result = $schema->validate([
            'public' => true,
            'secret' => 'i am secret'
        ]);

        $this->assertFalse($result->isValid());
        $this->assertEquals(['The secret field is prohibited when public is 1.'], $result->errors()['secret']);

        // Should pass
        $result = $schema->validate([
            'public' => false,
            'secret' => 'i am secret'
        ]);
        $this->assertTrue($result->isValid());
    }
}
