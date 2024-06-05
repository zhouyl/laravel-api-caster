<?php

namespace Mellivora\Http\Api\Tests;

use ArrayObject;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Collection;
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\Response;
use Mellivora\Http\Api\Tests\MockLib\Message;
use Mellivora\Http\Api\Tests\MockLib\StatusEnum;
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
        $psrResponse  = new PsrResponse($data['status'], $data['headers'], json_encode($data['body']));
        $httpResponse = new HttpResponse($psrResponse);

        return new Response($httpResponse);
    }

    /**
     * @dataProvider singleData
     */
    public function testEntity($data): void
    {
        $entity = Entity::from($this->getResponse($data));
        $body   = $data['body'];

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
        unset($copy2->value);
        $this->assertFalse(isset($copy2->value));
    }

    /**
     * @dataProvider multipleData
     */
    public function testCollection($data): void
    {
        $collection = Entity::collectionResponse($this->getResponse($data));
        $body       = $data['body'];

        $this->assertInstanceOf(Collection::class, $collection);

        foreach ($collection as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
            $this->assertEquals($body['meta'], $entity->meta());
            $this->assertEquals($body['meta']['locale'], $entity->meta('locale'));
            $this->assertFalse($entity->isEmpty());
        }
    }

    public function testCollectionException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Entity::collection([1, 2, 3]);
    }

    public function testNoCamelEntity(): void
    {
        $data   = ['product_id' => 123];
        $entity = new class($data) extends Entity {
            protected bool $useCamel = false;
        };

        $this->assertNull($entity->productId);
        $this->assertEquals($entity->product_id, $data['product_id']);
    }

    public function testMappingEntityException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new class(['brand' => ['id' => 1, 'name' => 'foo']]) extends Entity {
            protected array $mappings = [
                '*' => Message::class,
            ];
        };
    }

    public function testMappingCollectionException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new class(['brands' => ['brand' => ['id' => 1, 'name' => 'foo']]]) extends Entity {
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

        $entity = new class($data) extends Entity {
            protected array $mappings = [
                '*' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Entity::class, $entity->brand);
        $entity = new class($data) extends Entity {
            protected array $mappings = [
                'brand' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Entity::class, $entity->brand);

        $data   = ['brands' => [$data]];
        $entity = new class($data) extends Entity {
            protected array $mappings = [
                '*[]' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Collection::class, $entity->brands);
        $this->assertInstanceOf(Entity::class, $entity->brands->first());

        $entity = new class($data) extends Entity {
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

        $entity = new class($data, $meta) extends Entity {
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

        $origin                 = $data->getArrayCopy();
        $origin['productId']    = $data['product_id'];
        $origin['createTime']   = $data['create_time'];
        $origin['excludeField'] = $data['exclude_field'];
        unset($origin['product_id'], $origin['create_time'], $origin['exclude_field']);

        $rename              = $origin;
        $rename['id']        = $origin['productId'];
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
