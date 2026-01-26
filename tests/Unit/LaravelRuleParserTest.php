<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Laravel\LaravelRuleParser;

class LaravelRuleParserTest extends TestCase
{
    private LaravelRuleParser $parser;

    protected function setUp(): void
    {
        $this->parser = new LaravelRuleParser();
    }

    public function testParseRequiredRule(): void
    {
        $rules = $this->parser->parse('required');
        $this->assertCount(1, $rules);
    }

    public function testParseMultipleRules(): void
    {
        $rules = $this->parser->parse('required|string|max:255');
        $this->assertCount(3, $rules);
    }

    public function testParseRulesWithParameters(): void
    {
        $rules = $this->parser->parse('between:1,10');
        $this->assertCount(1, $rules);
    }

    public function testParseNewCoreTypeRules(): void
    {
        $rules = $this->parser->parse('numeric|boolean|array|date|json');
        $this->assertCount(5, $rules);
    }

    public function testParseNewStringRules(): void
    {
        $rules = $this->parser->parse('alpha|alpha_num|url|uuid|ip');
        $this->assertCount(5, $rules);
    }

    public function testParseNewComparisonRules(): void
    {
        $rules = $this->parser->parse('in:a,b,c|not_in:x,y,z|confirmed|same:other|different:another');
        $this->assertCount(5, $rules);
    }

    public function testParseDateWithFormat(): void
    {
        $rules = $this->parser->parse('date_format:Y-m-d');
        $this->assertCount(1, $rules);
    }

    public function testParseIpVersions(): void
    {
        $rules = $this->parser->parse('ip|ipv4|ipv6');
        $this->assertCount(3, $rules);
    }

    public function testParseFileRules(): void
    {
        $rules = $this->parser->parse('file|image|mimes:jpg,png');
        $this->assertCount(3, $rules);
    }

    public function testParseArrayDefinition(): void
    {
        $rules = $this->parser->parse(['required', 'string', 'max:100']);
        $this->assertCount(3, $rules);
    }

    public function testParseUnknownRuleReturnsNull(): void
    {
        $rules = $this->parser->parse('unknown_rule');
        $this->assertCount(0, $rules);
    }

    public function testParseSizeRules(): void
    {
        $rules = $this->parser->parse('min:5|max:10|size:7|between:3,8');
        $this->assertCount(4, $rules);
    }
}
