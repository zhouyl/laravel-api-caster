<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests;

use ArrayObject;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Collection;
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\EntityCollection;
use Mellivora\Http\Api\Response;
use Mellivora\Http\Api\Tests\MockLib\Message;
use Mellivora\Http\Api\Tests\MockLib\StatusEnum;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class EntityTest extends TestCase
{
    protected PsrResponse $psrResponse;

    protected HttpResponse $httpResponse;

    protected Response $response;

    public static function singleData(): array
    {
        return [[[
            'status'  => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => [
                'code'    => 200,
                'message' => 'OK',
                'data'    => ['id' => 123, 'name' => 'foo', 'brand' => ['name' => '3M']],
                'meta'    => ['locale' => 'zh-CN'],
            ],
        ]]];
    }

    public static function multipleData(): array
    {
        return [[[
            'status'  => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => [
                'code'    => 200,
                'message' => 'OK',
                'data'    => [
                    ['id' => 123, 'name' => 'foo', 'brand' => ['name' => '3M']],
                    ['id' => 124, 'name' => 'bar', 'brand' => ['name' => 'sony']],
                ],
                'meta' => ['locale' => 'zh-CN'],
            ],
        ]]];
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function getResponse(array $data): Response
    {
        $psrResponse = new PsrResponse($data['status'], $data['headers'], json_encode($data['body']));
        $httpResponse = new HttpResponse($psrResponse);

        return new Response($httpResponse);
    }

    /**
     * @dataProvider singleData
     */
    public function testEntity($data): void
    {
        $entity = Entity::from($this->getResponse($data));
        $body = $data['body'];

        $this->assertInstanceOf(Entity::class, $entity);

        $unserialized = unserialize(serialize($entity));
        $this->assertInstanceOf(Entity::class, $unserialized);
        $this->assertEquals($unserialized->toArray(), $entity->toArray());

        $this->assertEquals(array_keys($body['data']), $entity->keys());
        $this->assertEquals(array_values($body['data']), $entity->values());

        foreach ($entity as $key => $value) {
            $this->assertEquals($value, $body['data'][$key]);
        }

        $this->assertEquals($body['data'], $entity->toArray());
        $this->assertEquals($body['data'], $entity->jsonSerialize());
        $this->assertEquals(json_encode($body['data']), $entity->toJson());
        $this->assertEquals($body['meta'], $entity->meta());
        $this->assertEquals($body['meta']['locale'], $entity->meta('locale'));
        $this->assertFalse($entity->isEmpty());
        $this->assertEquals(count($entity), $entity->count());
        $this->assertEquals(count($body['data']), $entity->count());
        $this->assertEquals($body['data']['id'], $entity->id);
        $this->assertEquals($body['data']['name'], $entity->name);
        $this->assertEquals($body['data']['id'], $entity->origin('id'));
        $this->assertEquals($body['data']['name'], $entity->origin('name'));

        $entity['brand.logo'] = 'http://logo.url';
        $this->assertEquals($body['data']['brand']['id'], $entity['brand.id']);
        $this->assertEquals($body['data']['brand']['name'], $entity['brand.name']);
        $this->assertInstanceOf(Entity::class, $entity->get('brand.logo', 'https://logo.url'));
        $this->assertEquals('https://logo.url', $entity->set('brand.logo'));
        $this->assertTrue(isset($entity['brand.logo']));
        $this->assertTrue($entity->has('brand.logo'));

        $copy1 = $entity->copy();
        $this->assertEquals($entity->toArray(), $copy1->toArray());
        $this->assertEquals($copy1, $copy1->forget(['brand.name', 'brand.logo']));
        $this->assertNull($copy1['brand.name']);
        $this->assertNull($copy1['brand.logo']);

        unset($copy1['brand.logo']);
        $this->assertFalse(isset($copy1['brand.logo']));
        $this->assertInstanceOf(Entity::class, $copy1->remove('brand.name'));
        $this->assertFalse($copy1->has('brand.logo'));

        $copy2 = $entity->only(['brand.name', 'brand.logo']);
        $this->assertEquals(['brand'], $copy2->keys());

        $copy2->value = 9876.22;
        $this->assertEquals(9876.22, $copy2->value);
        $this->assertTrue(isset($copy2->value));
        $copy2->value = null;
        $this->assertFalse(isset($copy2->value));
    }

    /**
     * @dataProvider multipleData
     */
    public function testCollection($data): void
    {
        $collection = Entity::collectionResponse($this->getResponse($data));
        $body = $data['body'];

        $this->assertInstanceOf(Collection::class, $collection);

        // Test collection meta access
        $this->assertEquals($body['meta'], $collection->meta());
        $this->assertEquals($body['meta']['locale'], $collection->meta('locale'));
        $this->assertTrue($collection->hasMeta('locale'));
        $this->assertFalse($collection->hasMeta('nonexistent'));

        foreach ($collection as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
            $this->assertEquals($body['meta'], $entity->meta());
            $this->assertEquals($body['meta']['locale'], $entity->meta('locale'));
            $this->assertFalse($entity->isEmpty());
        }
    }

    public function testCollectionWithPaginationMeta(): void
    {
        $items = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
        ];

        $meta = [
            'total_items' => 123,
            'page' => 2,
            'per_page' => 10,
            'total_pages' => 13,
            'has_more' => true,
        ];

        $collection = Entity::collection($items, $meta);

        // Test meta access
        $this->assertEquals($meta, $collection->meta());
        $this->assertEquals(123, $collection->meta('total_items'));
        $this->assertEquals(2, $collection->meta('page'));
        $this->assertEquals(10, $collection->meta('per_page'));

        // Test pagination helpers
        $this->assertEquals(123, $collection->total());
        $this->assertEquals(2, $collection->currentPage());
        $this->assertEquals(10, $collection->perPage());
        $this->assertTrue($collection->hasMorePages());

        $pagination = $collection->pagination();
        $this->assertEquals(123, $pagination['total']);
        $this->assertEquals(2, $pagination['page']);
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(13, $pagination['total_pages']);
        $this->assertTrue($pagination['has_more']);

        // Test array with meta
        $arrayWithMeta = $collection->toArrayWithMeta();
        $this->assertArrayHasKey('data', $arrayWithMeta);
        $this->assertArrayHasKey('meta', $arrayWithMeta);
        $this->assertEquals($meta, $arrayWithMeta['meta']);
        $this->assertCount(2, $arrayWithMeta['data']);

        // Test JSON with meta
        $jsonWithMeta = $collection->toJsonWithMeta();
        $decoded = json_decode($jsonWithMeta, true);
        $this->assertEquals($arrayWithMeta, $decoded);
    }

    public function testEntityCollectionTypeSafety(): void
    {
        $items = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
        ];

        $collection = Entity::collection($items);

        // Test that all modification methods enforce type safety
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected instance of');

        // Test put method
        $collection->put('invalid', ['not' => 'entity']);
    }

    public function testEntityCollectionPutTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->put('key', ['invalid' => 'data']);
    }

    public function testEntityCollectionPrependTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->prepend(['invalid' => 'data']);
    }

    public function testEntityCollectionMergeTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->merge([['invalid' => 'data']]);
    }

    public function testEntityCollectionConcatTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->concat([['invalid' => 'data']]);
    }

    public function testEntityCollectionReplaceTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->replace([['invalid' => 'data']]);
    }

    public function testEntityCollectionSpliceTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->splice(0, 1, [['invalid' => 'data']]);
    }

    public function testEntityCollectionOffsetSetTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection[0] = ['invalid' => 'data'];
    }

    public function testEntityCollectionMapTypeSafety(): void
    {
        $collection = Entity::collection([['id' => 1, 'name' => 'User 1']]);

        $this->expectException(UnexpectedValueException::class);
        $collection->map(fn ($entity) => ['invalid' => 'data']);
    }

    public function testEntityCollectionValidOperations(): void
    {
        $items = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
        ];

        // Test valid put
        $collection1 = Entity::collection($items, ['total' => 10]);
        $newEntity = new Entity(['id' => 3, 'name' => 'User 3']);
        $newCollection = $collection1->put('new', $newEntity);
        $this->assertInstanceOf(EntityCollection::class, $newCollection);
        $this->assertEquals(3, $newCollection->count());

        // Test valid prepend
        $collection2 = Entity::collection($items, ['total' => 10]);
        $prependEntity = new Entity(['id' => 0, 'name' => 'User 0']);
        $prependedCollection = $collection2->prepend($prependEntity);
        $this->assertInstanceOf(EntityCollection::class, $prependedCollection);
        $this->assertEquals(3, $prependedCollection->count());

        // Test valid merge
        $collection3 = Entity::collection($items, ['total' => 10]);
        $mergeEntities = [new Entity(['id' => 4, 'name' => 'User 4'])];
        $mergedCollection = $collection3->merge($mergeEntities);
        $this->assertInstanceOf(EntityCollection::class, $mergedCollection);
        $this->assertEquals(3, $mergedCollection->count());

        // Test that meta is preserved in new collections
        $collection4 = Entity::collection($items, ['total' => 10]);
        $filteredCollection = $collection4->filter(fn ($entity) => $entity->id > 1);
        $this->assertInstanceOf(EntityCollection::class, $filteredCollection);
        $this->assertEquals(10, $filteredCollection->meta('total'));
        $this->assertEquals(1, $filteredCollection->count());

        // Test map with valid Entity return
        $collection5 = Entity::collection($items, ['total' => 10]);
        $mappedCollection = $collection5->map(fn ($entity) => new Entity([
            'id' => $entity->id,
            'name' => strtoupper($entity->name),
        ]));
        $this->assertInstanceOf(EntityCollection::class, $mappedCollection);
        $this->assertEquals(10, $mappedCollection->meta('total'));
        $this->assertEquals('USER 1', $mappedCollection->first()->name);
    }

    public function testCollectionException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Entity::collection([1, 2, 3]);
    }

    public function testNoCamelEntity(): void
    {
        $data = ['product_id' => 123];
        $entity = new class ($data) extends Entity {
            protected bool $useCamel = false;
        };

        $this->assertNull($entity->productId);
        $this->assertEquals($entity->product_id, $data['product_id']);
    }

    public function testMappingEntityException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new class (['brand' => ['id' => 1, 'name' => 'foo']]) extends Entity {
            protected array $mappings = [
                '*' => Message::class,
            ];
        };
    }

    public function testMappingCollectionException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new class (['brands' => ['brand' => ['id' => 1, 'name' => 'foo']]]) extends Entity {
            protected array $mappings = [
                '*[]' => Message::class,
            ];
        };
    }

    public function testMappingEntity(): void
    {
        $data = [
            'brand' => ['id' => 1, 'name' => 'foo'],
        ];

        $entity = new class ($data) extends Entity {
            protected array $mappings = [
                '*' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Entity::class, $entity->brand);
        $entity = new class ($data) extends Entity {
            protected array $mappings = [
                'brand' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Entity::class, $entity->brand);

        $data = ['brands' => [$data]];
        $entity = new class ($data) extends Entity {
            protected array $mappings = [
                '*[]' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Collection::class, $entity->brands);
        $this->assertInstanceOf(Entity::class, $entity->brands->first());

        $entity = new class ($data) extends Entity {
            protected array $mappings = [
                'brands[]' => Entity::class,
            ];
        };
        $this->assertInstanceOf(Collection::class, $entity->brands);
        $this->assertInstanceOf(Entity::class, $entity->brands->first());
    }

    public function testCustomerEntity(): void
    {
        $data = new ArrayObject([
            'product_id' => 123,
            'name'       => 'foo',
            'status'     => 1,
            'brand'      => ['name' => '3M'],
            'attributes' => [
                ['id' => 1, 'name' => 'foo'],
                ['id' => 2, 'name' => 'bar'],
            ],
            'create_time'   => '2024-05-21 01:02:03',
            'exclude_field' => 'xxx',
        ]);

        $meta = ['locale' => 'zh-CN'];

        $entity = new class ($data, $meta) extends Entity {
            protected array $renames = ['productId' => 'id'];

            protected array $casts = ['status' => StatusEnum::class, 'createTime' => 'datetime'];

            protected array $mappings = ['attributes[]' => Entity::class];

            protected array $appends = ['brandName'];

            protected array $excludes = ['excludeField'];

            public function getBrandNameAttribute(): string
            {
                return $this->origin('brand.name');
            }
        };

        $origin = $data->getArrayCopy();
        $origin['productId'] = $data['product_id'];
        $origin['createTime'] = $data['create_time'];
        $origin['excludeField'] = $data['exclude_field'];
        unset($origin['product_id'], $origin['create_time'], $origin['exclude_field']);

        $rename = $origin;
        $rename['id'] = $origin['productId'];
        $rename['brandName'] = $origin['brand']['name'];
        unset($rename['productId'], $rename['excludeField']);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals($origin, $entity->origin());
        $this->assertEquals($rename, $entity->toArray());
        $this->assertEquals($data['product_id'], $entity->id);
        $this->assertEquals($data['brand']['name'], $entity->brandName);
        $this->assertInstanceOf(StatusEnum::class, $entity->status);
        $this->assertInstanceOf(Carbon::class, $entity->createTime);
        $this->assertInstanceOf(Collection::class, $entity->attributes);
        $this->assertInstanceOf(Entity::class, $entity->attributes->first());
    }
}
