<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Collection;
use Mellivora\Http\Api\Caster;
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\Response;
use Mellivora\Http\Api\Tests\MockLib\StatusEnum;

/**
 * Test class to verify that documentation examples work correctly.
 */
class DocumentationExamplesTest extends TestCase
{
    public function testBasicEntityUsage(): void
    {
        // Example from Entity PHPDoc
        $entity = new Entity(['id' => 1, 'name' => 'John']);

        $this->assertEquals(1, $entity->id);
        $this->assertEquals('John', $entity->name);
    }

    public function testEntityFromResponse(): void
    {
        // Example from Entity::from() PHPDoc
        $psrResponse = new PsrResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'code'    => 200,
                'message' => 'OK',
                'data'    => ['id' => 123, 'name' => 'John Doe'],
                'meta'    => ['version' => '1.0'],
            ])
        );

        $httpResponse = new HttpResponse($psrResponse);
        $response = new Response($httpResponse);
        $user = Entity::from($response);

        $this->assertEquals(123, $user->id);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('1.0', $user->meta('version'));
    }

    public function testEntityCollection(): void
    {
        // Example from Entity::collection() PHPDoc
        $users = Entity::collection([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);
        $this->assertEquals(1, $users->first()->id);
        $this->assertEquals('John', $users->first()->name);
        $this->assertEquals(2, $users->last()->id);
        $this->assertEquals('Jane', $users->last()->name);
    }

    public function testCasterExamples(): void
    {
        $caster = new Caster();

        // Examples from Caster::cast() PHPDoc
        $this->assertEquals(123, $caster->cast('int', '123'));
        $this->assertInstanceOf(Carbon::class, $caster->cast('datetime', '2023-01-01'));
        $this->assertEquals('123.46', $caster->cast('decimal:2', '123.456'));
    }

    public function testEntityWithCasts(): void
    {
        // Example from Entity casts property PHPDoc
        $entity = new class (['id' => '123', 'status' => 1, 'created_at' => '2023-01-01 12:00:00']) extends Entity {
            protected array $casts = [
                'id'         => 'int',
                'status'     => StatusEnum::class,
                'created_at' => 'datetime',
            ];
        };

        $this->assertIsInt($entity->id);
        $this->assertEquals(123, $entity->id);
        $this->assertInstanceOf(StatusEnum::class, $entity->status);
        $this->assertEquals(StatusEnum::ONE, $entity->status);
        $this->assertInstanceOf(Carbon::class, $entity->createdAt);
    }

    public function testEntityWithMappings(): void
    {
        // Example from Entity mappings property PHPDoc
        $entity = new class ([
            'user'       => ['id' => 1, 'name' => 'John'],
            'categories' => [
                ['id' => 1, 'name' => 'Tech'],
                ['id' => 2, 'name' => 'News'],
            ],
        ]) extends Entity {
            protected array $mappings = [
                'user'         => Entity::class,
                'categories[]' => Entity::class,
            ];
        };

        $this->assertInstanceOf(Entity::class, $entity->user);
        $this->assertEquals(1, $entity->user->id);
        $this->assertEquals('John', $entity->user->name);

        $this->assertInstanceOf(Collection::class, $entity->categories);
        $this->assertCount(2, $entity->categories);
        $this->assertInstanceOf(Entity::class, $entity->categories->first());
        $this->assertEquals('Tech', $entity->categories->first()->name);
    }

    public function testEntityWithAppends(): void
    {
        // Example from Entity appends property PHPDoc
        $entity = new class (['first_name' => 'John', 'last_name' => 'Doe']) extends Entity {
            protected array $appends = ['fullName'];

            public function getFullNameAttribute(): string
            {
                return $this->firstName . ' ' . $this->lastName;
            }
        };

        $this->assertEquals('John Doe', $entity->fullName);
        $this->assertArrayHasKey('fullName', $entity->toArray());
    }

    public function testEntityWithIncludes(): void
    {
        // Example from Entity includes property PHPDoc
        $entity = new class (['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'password' => 'secret']) extends Entity {
            protected array $includes = ['id', 'name', 'email'];
        };

        $array = $entity->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayNotHasKey('password', $array);
    }

    public function testEntityWithExcludes(): void
    {
        // Example from Entity excludes property PHPDoc
        $entity = new class (['id' => 1, 'name' => 'John', 'password' => 'secret', 'secret_key' => 'key123']) extends Entity {
            protected array $excludes = ['password', 'secretKey'];
        };

        $array = $entity->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('secretKey', $array);
    }

    public function testEntityWithRenames(): void
    {
        // Example from Entity renames property PHPDoc
        $entity = new class (['user_id' => 123, 'full_name' => 'John Doe']) extends Entity {
            protected array $renames = [
                'userId'   => 'id',      // renames work on camelCase keys
                'fullName' => 'name',
            ];
        };

        $array = $entity->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('userId', $array);
        $this->assertArrayNotHasKey('fullName', $array);
        $this->assertEquals(123, $entity->id);
        $this->assertEquals('John Doe', $entity->name);
    }

    public function testResponseMethods(): void
    {
        // Test Response class examples
        $psrResponse = new PsrResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'code'    => 200,
                'message' => 'Success',
                'data'    => ['user' => ['id' => 1, 'name' => 'John']],
                'meta'    => ['total' => 100, 'page' => 1],
            ])
        );

        $httpResponse = new HttpResponse($psrResponse);
        $response = new Response($httpResponse);

        $this->assertEquals(200, $response->code());
        $this->assertEquals('Success', $response->message());
        $this->assertEquals(['user' => ['id' => 1, 'name' => 'John']], $response->data());
        $this->assertEquals(['total' => 100, 'page' => 1], $response->meta());
        $this->assertEquals(100, $response->meta('total'));
        $this->assertEquals(1, $response->data('user.id'));
    }
}
