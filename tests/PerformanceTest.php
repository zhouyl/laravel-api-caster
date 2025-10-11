<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests;

use Mellivora\Http\Api\Caster;
use Mellivora\Http\Api\Entity;

class PerformanceTest extends TestCase
{
    public function testEntityCreationPerformance(): void
    {
        $data = [];

        for ($i = 0; $i < 100; $i++) {
            $data["field_$i"] = "value_$i";
        }

        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            $entity = new Entity($data);
            $this->assertInstanceOf(Entity::class, $entity);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust threshold as needed)
        $this->assertLessThan(1.0, $executionTime, 'Entity creation took too long');
    }

    public function testCasterPerformance(): void
    {
        $caster = new Caster();
        $testData = [
            'string_value' => 123,
            'int_value'    => '456',
            'float_value'  => '123.456',
            'bool_value'   => 1,
            'json_value'   => '{"key": "value"}',
        ];

        $startTime = microtime(true);

        for ($i = 0; $i < 1000; $i++) {
            $caster->cast('string', $testData['string_value']);
            $caster->cast('int', $testData['int_value']);
            $caster->cast('float', $testData['float_value']);
            $caster->cast('bool', $testData['bool_value']);
            $caster->cast('json', $testData['json_value']);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time
        $this->assertLessThan(1.0, $executionTime, 'Caster operations took too long');
    }

    public function testLargeDatasetHandling(): void
    {
        // Create a moderately large dataset that doesn't exceed our safety limits
        $data = [];

        for ($i = 0; $i < 1000; $i++) {
            $data["key$i"] = [
                'id'     => $i,
                'name'   => "name_$i",
                'value'  => rand(1, 1000),
                'nested' => [
                    'level1' => "level1_$i",
                    'level2' => [
                        'deep_value' => "deep_$i",
                    ],
                ],
            ];
        }

        $startTime = microtime(true);

        $entity = new Entity($data);

        // Test access patterns
        $this->assertEquals(0, $entity->offsetGet('key0.id'));
        $this->assertEquals('name_500', $entity->offsetGet('key500.name'));
        $this->assertEquals('deep_999', $entity->offsetGet('key999.nested.level2.deepValue'));

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should handle large datasets efficiently
        $this->assertLessThan(2.0, $executionTime, 'Large dataset handling took too long');
    }

    public function testMemoryUsage(): void
    {
        $initialMemory = memory_get_usage();

        $entities = [];

        for ($i = 0; $i < 100; $i++) {
            $data = [
                'id'   => $i,
                'name' => "entity_$i",
                'data' => array_fill(0, 100, "value_$i"),
            ];
            $entities[] = new Entity($data);
        }

        $peakMemory = memory_get_peak_usage();
        $memoryUsed = $peakMemory - $initialMemory;

        // Clean up
        unset($entities);

        // Memory usage should be reasonable (adjust threshold as needed)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage too high'); // 50MB
    }
}
