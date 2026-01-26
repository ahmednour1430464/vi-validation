<?php

declare(strict_types=1);

namespace Vi\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vi\Validation\Cache\ArraySchemaCache;
use Vi\Validation\Execution\CompiledSchema;
use Vi\Validation\Schema\FieldDefinition;

class CacheTest extends TestCase
{
    public function testArrayCachePutAndGet(): void
    {
        $cache = new ArraySchemaCache();
        $schema = $this->createMockSchema();
        
        $cache->put('test-key', $schema);
        
        $this->assertTrue($cache->has('test-key'));
        $this->assertInstanceOf(CompiledSchema::class, $cache->get('test-key'));
    }

    public function testArrayCacheForget(): void
    {
        $cache = new ArraySchemaCache();
        $schema = $this->createMockSchema();
        
        $cache->put('test-key', $schema);
        $cache->forget('test-key');
        
        $this->assertFalse($cache->has('test-key'));
        $this->assertNull($cache->get('test-key'));
    }

    public function testArrayCacheFlush(): void
    {
        $cache = new ArraySchemaCache();
        $schema = $this->createMockSchema();
        
        $cache->put('key1', $schema);
        $cache->put('key2', $schema);
        $cache->flush();
        
        $this->assertEquals(0, $cache->count());
    }

    public function testArrayCacheTtlExpiration(): void
    {
        $cache = new ArraySchemaCache();
        $schema = $this->createMockSchema();
        
        // Set TTL to 1 second
        $cache->put('test-key', $schema, 1);
        
        $this->assertTrue($cache->has('test-key'));
        
        // Wait for expiration
        sleep(2);
        
        $this->assertFalse($cache->has('test-key'));
    }

    public function testArrayCacheKeys(): void
    {
        $cache = new ArraySchemaCache();
        $schema = $this->createMockSchema();
        
        $cache->put('key1', $schema);
        $cache->put('key2', $schema);
        
        $keys = $cache->keys();
        $this->assertContains('key1', $keys);
        $this->assertContains('key2', $keys);
    }

    private function createMockSchema(): CompiledSchema
    {
        return CompiledSchema::fromFieldDefinitions([]);
    }
}
