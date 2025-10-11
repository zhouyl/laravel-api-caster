<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mellivora\Http\Api\Caster;
use Mellivora\Http\Api\Entity;
use stdClass;
use UnexpectedValueException;

class EdgeCasesTest extends TestCase
{
    public function testEntityWithEmptyData(): void
    {
        $entity = new Entity();

        $this->assertTrue($entity->isEmpty());
        $this->assertCount(0, $entity);
        $this->assertEquals([], $entity->toArray());
        $this->assertEquals('[]', $entity->toJson());
        $this->assertEquals([], $entity->keys());
        $this->assertEquals([], $entity->values());
    }

    public function testEntityWithNullValues(): void
    {
        $data = [
            'id'     => null,
            'name'   => null,
            'status' => null,
        ];

        $entity = new Entity($data);

        $this->assertFalse($entity->isEmpty());
        $this->assertNull($entity->id);
        $this->assertNull($entity->name);
        $this->assertNull($entity->status);
        $this->assertEquals($data, $entity->toArray());
    }

    public function testEntityWithDeepNestedData(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'deep_value',
                    ],
                ],
            ],
        ];

        $entity = new Entity($data);

        $this->assertEquals('deep_value', $entity->offsetGet('level1.level2.level3.value'));
        $this->assertTrue($entity->offsetExists('level1.level2.level3.value'));
        $this->assertFalse($entity->offsetExists('level1.level2.level3.nonexistent'));
    }

    public function testEntityCopy(): void
    {
        $data = ['id' => 123, 'name' => 'test'];
        $entity = new Entity($data);
        $copy = $entity->copy();

        $this->assertEquals($entity->toArray(), $copy->toArray());
        $this->assertNotSame($entity, $copy);

        // Modify original
        $entity->offsetSet('name', 'modified');
        $this->assertEquals('modified', $entity->name);
        $this->assertEquals('test', $copy->name);
    }

    public function testEntityWithComplexCasts(): void
    {
        $data = [
            'timestamp'       => '2023-01-01 12:00:00',
            'json_data'       => '{"key": "value"}',
            'collection_data' => '[1, 2, 3]',
            'decimal_value'   => '123.456',
        ];

        $entity = new class ($data) extends Entity {
            protected array $casts = [
                'timestamp'      => 'datetime',
                'jsonData'       => 'json',
                'collectionData' => 'collection',
                'decimalValue'   => 'decimal:2',
            ];
        };

        $this->assertInstanceOf(Carbon::class, $entity->timestamp);
        $this->assertIsArray($entity->jsonData);
        $this->assertEquals('value', $entity->jsonData['key']);
        $this->assertInstanceOf(Collection::class, $entity->collectionData);
        $this->assertEquals([1, 2, 3], $entity->collectionData->toArray());
        $this->assertEquals('123.46', $entity->decimalValue);
    }

    public function testCasterWithInvalidDecimal(): void
    {
        $caster = new Caster();

        $this->expectException(\Illuminate\Support\Exceptions\MathException::class);
        $caster->cast('decimal:2', 'invalid_number');
    }

    public function testEntityWithInvalidMapping(): void
    {
        $this->expectException(UnexpectedValueException::class);

        new class (['data' => ['test' => 'value']]) extends Entity {
            protected array $mappings = [
                'data' => stdClass::class,
            ];
        };
    }

    public function testEntityWithCircularReference(): void
    {
        $data = ['id' => 1, 'name' => 'test'];
        $entity = new Entity($data);

        // Test that entity can handle itself in arrays
        $entity->offsetSet('self', $entity);

        $this->assertInstanceOf(Entity::class, $entity->self);
        $this->assertEquals(1, $entity->self->id);
    }

    public function testEntityWithLargeDataset(): void
    {
        $data = [];

        for ($i = 0; $i < 1000; $i++) {
            $data["key$i"] = "value_$i";
        }

        $entity = new Entity($data);

        $this->assertCount(1000, $entity);
        $this->assertEquals('value_500', $entity->offsetGet('key500'));
        $this->assertTrue($entity->offsetExists('key999'));
        $this->assertFalse($entity->offsetExists('key1000'));
    }

    public function testEntityWithSpecialCharacters(): void
    {
        $data = [
            'unicodeKey测试' => 'unicode_value_测试',
            'emojiKey🚀'     => 'emoji_value_🎉',
            'specialChars'   => 'special_value_^&*()',
        ];

        $entity = new Entity($data);

        $this->assertEquals('unicode_value_测试', $entity->offsetGet('unicodeKey测试'));
        $this->assertEquals('emoji_value_🎉', $entity->offsetGet('emojiKey🚀'));
        $this->assertEquals('special_value_^&*()', $entity->offsetGet('specialChars'));
    }

    public function testEntityIteratorBehavior(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $entity = new Entity($data);

        $result = [];

        foreach ($entity as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals($data, $result);
    }

    public function testEntityMagicMethods(): void
    {
        $entity = new Entity(['test' => 'value']);

        // Test __get and __set
        $this->assertEquals('value', $entity->test);
        $entity->new_key = 'new_value';
        $this->assertEquals('new_value', $entity->new_key);

        // Test __isset and __unset
        $this->assertTrue(isset($entity->test));
        $entity->test = null;
        $this->assertFalse(isset($entity->test));
    }
}
