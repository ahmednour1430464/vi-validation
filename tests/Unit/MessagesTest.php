<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Messages\Translator;
use Vi\Validation\Messages\MessageBag;
use Vi\Validation\Messages\MessageResolver;

class MessagesTest extends TestCase
{
    public function testTranslatorGetMessage(): void
    {
        $translator = new Translator('en');
        $message = $translator->get('required');
        
        $this->assertStringContainsString(':attribute', $message);
        $this->assertStringContainsString('required', $message);
    }

    public function testTranslatorReplacesPlaceholders(): void
    {
        $translator = new Translator('en');
        $message = $translator->get('required', ['attribute' => 'email']);
        
        $this->assertStringContainsString('email', $message);
        $this->assertStringNotContainsString(':attribute', $message);
    }

    public function testTranslatorSetLocale(): void
    {
        $translator = new Translator('en');
        $translator->setLocale('ar');
        
        $this->assertEquals('ar', $translator->getLocale());
    }

    public function testTranslatorAddMessages(): void
    {
        $translator = new Translator('en');
        $translator->addMessages(['custom' => 'Custom message'], 'en');
        
        $message = $translator->get('custom');
        $this->assertEquals('Custom message', $message);
    }

    public function testMessageBagAddAndRetrieve(): void
    {
        $bag = new MessageBag();
        $bag->add('email', 'Email is invalid');
        $bag->add('email', 'Email is required');
        
        $this->assertTrue($bag->has('email'));
        $this->assertCount(2, $bag->get('email'));
    }

    public function testMessageBagFirst(): void
    {
        $bag = new MessageBag();
        $bag->add('name', 'Name is required');
        $bag->add('email', 'Email is invalid');
        
        $this->assertEquals('Name is required', $bag->first());
        $this->assertEquals('Email is invalid', $bag->first('email'));
    }

    public function testMessageBagCount(): void
    {
        $bag = new MessageBag();
        $bag->add('name', 'Error 1');
        $bag->add('name', 'Error 2');
        $bag->add('email', 'Error 3');
        
        $this->assertEquals(3, $bag->count());
    }

    public function testMessageBagIsEmpty(): void
    {
        $bag = new MessageBag();
        $this->assertTrue($bag->isEmpty());
        
        $bag->add('field', 'error');
        $this->assertFalse($bag->isEmpty());
        $this->assertTrue($bag->isNotEmpty());
    }

    public function testMessageResolverBasic(): void
    {
        $resolver = new MessageResolver();
        $message = $resolver->resolve('email', 'required');
        
        $this->assertStringContainsString('email', $message);
        $this->assertStringContainsString('required', $message);
    }

    public function testMessageResolverCustomMessages(): void
    {
        $resolver = new MessageResolver();
        $resolver->setCustomMessages([
            'email.required' => 'Please enter your email address'
        ]);
        
        $message = $resolver->resolve('email', 'required');
        $this->assertEquals('Please enter your email address', $message);
    }

    public function testMessageResolverCustomAttributes(): void
    {
        $resolver = new MessageResolver();
        $resolver->setCustomAttributes([
            'email_address' => 'email'
        ]);
        
        $message = $resolver->resolve('email_address', 'required');
        $this->assertStringContainsString('email', $message);
    }

    public function testMessageResolverFormatsUnderscoreAttribute(): void
    {
        $resolver = new MessageResolver();
        $message = $resolver->resolve('user_email', 'required');
        
        $this->assertStringContainsString('user email', $message);
    }
}
