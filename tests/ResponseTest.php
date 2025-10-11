<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response as HttpResponse;
use LogicException;
use Mellivora\Http\Api\Response;

class ResponseTest extends TestCase
{
    protected array $data = [
        'status'  => 200,
        'headers' => [
            'Content-Type' => 'application/json',
            'locale'       => 'zh-CN',
        ],
        'body' => [
            'code'    => 200,
            'message' => 'OK',
            'data'    => ['foo' => ['bar' => 789]],
            'meta'    => ['foz' => ['baz' => 456.123]],
        ],
    ];

    protected PsrResponse $psrResponse;

    protected HttpResponse $httpResponse;

    protected Response $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->psrResponse = new PsrResponse($this->data['status'], $this->data['headers'], json_encode($this->data['body']));
        $this->httpResponse = new HttpResponse($this->psrResponse);
        $this->response = new Response($this->httpResponse);
    }

    public function testResponse()
    {
        $this->assertSame($this->httpResponse->toPsrResponse(), $this->psrResponse);
        $this->assertSame($this->httpResponse->toPsrResponse(), $this->response->toPsrResponse());

        $unserialized = unserialize(serialize($this->response));

        $this->assertInstanceOf(Response::class, $unserialized);
        $this->assertSame($this->response->status(), $unserialized->status());
        $this->assertSame($this->response->headers(), $unserialized->headers());
        $this->assertSame($this->response->body(), $unserialized->body());
        $this->assertSame($this->response->json(), $unserialized->json());
        $this->assertSame($this->response->code(), $unserialized->code());
        $this->assertSame($this->response->message(), $unserialized->message());
        $this->assertSame($this->response->meta(), $unserialized->meta());
        $this->assertSame($this->response->data(), $unserialized->data());

        $this->assertSame($this->response->code(), $this->data['body']['code']);
        $this->assertSame($this->response->message(), $this->data['body']['message']);
        $this->assertSame($this->response->meta(), $this->data['body']['meta']);
        $this->assertSame($this->response->meta('foz'), $this->data['body']['meta']['foz']);
        $this->assertSame($this->response->meta('foz.baz'), $this->data['body']['meta']['foz']['baz']);
        $this->assertSame($this->response->data(), $this->data['body']['data']);
        $this->assertSame($this->response->data('foo'), $this->data['body']['data']['foo']);
        $this->assertSame($this->response->data('foo.bar'), $this->data['body']['data']['foo']['bar']);

        $this->assertSame($this->response['code'], $this->data['body']['code']);
        $this->assertSame($this->response['message'], $this->data['body']['message']);
        $this->assertSame($this->response['meta'], $this->data['body']['meta']);
        $this->assertSame($this->response['data'], $this->data['body']['data']);

        $this->assertTrue(isset($this->response['code']));
        $this->assertTrue(isset($this->response['message']));
        $this->assertTrue(isset($this->response['data']));
        $this->assertTrue(isset($this->response['meta']));
        $this->assertFalse(isset($this->response['not_exists']));

        $this->assertSame($unserialized->toArray(), $this->response->toArray());
    }

    public function testOffsetSetException()
    {
        $this->expectException(LogicException::class);
        $this->response['code'] = 500;
    }

    public function testOffsetUnsetException()
    {
        $this->expectException(LogicException::class);
        unset($this->response['code']);
    }
}
