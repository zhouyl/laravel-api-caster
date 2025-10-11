<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Collection;
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\Response;
use Mellivora\Http\Api\Tests\MockLib\StatusEnum;

class IntegrationTest extends TestCase
{
    public function testCompleteWorkflow(): void
    {
        // Simulate a complete API response workflow
        $apiResponseData = [
            'code'    => 200,
            'message' => 'Success',
            'data'    => [
                'user' => [
                    'id'         => 123,
                    'name'       => 'John Doe',
                    'email'      => 'john@example.com',
                    'status'     => '1',
                    'created_at' => '2023-01-01 12:00:00',
                    'profile'    => [
                        'avatar' => 'https://example.com/avatar.jpg',
                        'bio'    => 'Software Developer',
                    ],
                    'roles' => [
                        ['id' => 1, 'name' => 'admin'],
                        ['id' => 2, 'name' => 'user'],
                    ],
                    'settings' => '{"theme": "dark", "notifications": true}',
                    'score'    => '95.75',
                ],
            ],
            'meta' => [
                'version'   => '2.0.0',
                'timestamp' => time(),
                'locale'    => 'en-US',
            ],
        ];

        // Create PSR response
        $psrResponse = new PsrResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($apiResponseData)
        );

        // Create HTTP response
        $httpResponse = new HttpResponse($psrResponse);

        // Create our custom Response
        $response = new Response($httpResponse);

        // Test Response methods
        $this->assertEquals(200, $response->code());
        $this->assertEquals('Success', $response->message());
        $this->assertEquals($apiResponseData['data'], $response->data());
        $this->assertEquals($apiResponseData['meta'], $response->meta());
        $this->assertEquals('2.0.0', $response->meta('version'));

        // Create Entity with complex mappings and casts
        $userEntity = new class ($response->data('user'), $response->meta()) extends Entity {
            protected array $casts = [
                'status'    => StatusEnum::class,
                'createdAt' => 'datetime',
                'settings'  => 'json',
                'score'     => 'decimal:2',
            ];

            protected array $mappings = [
                'profile' => Entity::class,
                'roles[]' => Entity::class,
            ];

            protected array $appends = ['display_name', 'is_admin'];

            public function getDisplayNameAttribute(): string
            {
                return $this->name . ' (' . $this->email . ')';
            }

            public function getIsAdminAttribute(): bool
            {
                return $this->roles->contains('name', 'admin');
            }
        };

        // Test Entity properties
        $this->assertEquals(123, $userEntity->id);
        $this->assertEquals('John Doe', $userEntity->name);
        $this->assertEquals('john@example.com', $userEntity->email);
        $this->assertInstanceOf(StatusEnum::class, $userEntity->status);
        $this->assertEquals(StatusEnum::ONE, $userEntity->status);
        $this->assertInstanceOf(\Carbon\Carbon::class, $userEntity->createdAt);
        $this->assertIsArray($userEntity->settings);
        $this->assertEquals('dark', $userEntity->settings['theme']);
        $this->assertTrue($userEntity->settings['notifications']);
        $this->assertEquals('95.75', $userEntity->score);

        // Test mapped entities
        $this->assertInstanceOf(Entity::class, $userEntity->profile);
        $this->assertEquals('https://example.com/avatar.jpg', $userEntity->profile->avatar);
        $this->assertEquals('Software Developer', $userEntity->profile->bio);

        $this->assertInstanceOf(Collection::class, $userEntity->roles);
        $this->assertCount(2, $userEntity->roles);
        $this->assertInstanceOf(Entity::class, $userEntity->roles->first());
        $this->assertEquals('admin', $userEntity->roles->first()->name);

        // Test appended attributes
        $this->assertEquals('John Doe (john@example.com)', $userEntity->display_name);
        $this->assertTrue($userEntity->is_admin);

        // Test meta data
        $this->assertEquals('2.0.0', $userEntity->meta('version'));
        $this->assertEquals('en-US', $userEntity->meta('locale'));

        // Test serialization (skip for anonymous classes)
        // Anonymous classes cannot be serialized, so we test with a regular Entity
        $regularEntity = new Entity($userEntity->toArray(), $userEntity->meta());
        $serialized = serialize($regularEntity);
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(Entity::class, $unserialized);
        $this->assertEquals($regularEntity->toArray(), $unserialized->toArray());

        // Test JSON conversion
        $json = $userEntity->toJson();
        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals($userEntity->id, $decoded['id']);
        $this->assertEquals($userEntity->display_name, $decoded['display_name']);
    }

    public function testCollectionWorkflow(): void
    {
        $apiResponseData = [
            'code'    => 200,
            'message' => 'Success',
            'data'    => [
                [
                    'id'         => 1,
                    'title'      => 'First Post',
                    'status'     => '1',
                    'created_at' => '2023-01-01 12:00:00',
                ],
                [
                    'id'         => 2,
                    'title'      => 'Second Post',
                    'status'     => '2',
                    'created_at' => '2023-01-02 12:00:00',
                ],
            ],
            'meta' => [
                'total'    => 2,
                'page'     => 1,
                'per_page' => 10,
            ],
        ];

        $psrResponse = new PsrResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($apiResponseData)
        );

        $httpResponse = new HttpResponse($psrResponse);
        $response = new Response($httpResponse);

        // Create collection of entities
        $posts = Entity::collectionResponse($response);

        $this->assertInstanceOf(Collection::class, $posts);
        $this->assertCount(2, $posts);

        foreach ($posts as $post) {
            $this->assertInstanceOf(Entity::class, $post);
            $this->assertIsInt($post->id);
            $this->assertIsString($post->title);
            $this->assertEquals(2, $post->meta('total'));
        }

        // Test collection methods
        $firstPost = $posts->first();
        $this->assertEquals(1, $firstPost->id);
        $this->assertEquals('First Post', $firstPost->title);

        $lastPost = $posts->last();
        $this->assertEquals(2, $lastPost->id);
        $this->assertEquals('Second Post', $lastPost->title);
    }

    public function testErrorHandling(): void
    {
        $errorResponseData = [
            'code'    => 400,
            'message' => 'Bad Request',
            'data'    => [],
            'meta'    => [
                'errors' => [
                    'field1' => ['Field is required'],
                    'field2' => ['Field must be valid'],
                ],
            ],
        ];

        $psrResponse = new PsrResponse(
            400,
            ['Content-Type' => 'application/json'],
            json_encode($errorResponseData)
        );

        $httpResponse = new HttpResponse($psrResponse);
        $response = new Response($httpResponse);

        $this->assertEquals(400, $response->code());
        $this->assertEquals('Bad Request', $response->message());
        $this->assertEquals([], $response->data());
        $this->assertIsArray($response->meta('errors'));
        $this->assertCount(2, $response->meta('errors'));
    }
}
