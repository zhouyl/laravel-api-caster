<?php

namespace Mellivora\Http\Api;

use ArrayAccess;
use Illuminate\Http\Client\Response as HttpResponse;
use LogicException;
use Psr\Http\Message\MessageInterface;
use Serializable;

/**
 * 重写 HTTP 响应结果
 * 根据网关 API 规范，新增了 code/message/data 方法
 */
class Response extends HttpResponse implements ArrayAccess, Serializable
{
    /**
     * Create a new response instance.
     *
     * @param HttpResponse|MessageInterface $response
     *
     * @return void
     */
    public function __construct($response)
    {
        if ($response instanceof HttpResponse) {
            $response = $response->toPsrResponse();
        }

        parent::__construct($response);
    }

    /**
     * @{inheritDoc}
     */
    public function __serialize(): array
    {
        return [
            'response' => get_class($this->response),
            'status'   => $this->status(),
            'headers'  => $this->headers(),
            'body'     => $this->body(),
        ];
    }

    /**
     * @{inheritDoc}
     */
    public function __unserialize(array $data): void
    {
        $this->response = new $data['response'](
            $data['status'],
            $data['headers'],
            $data['body'],
        );
    }

    /**
     * @codeCoverageIgnore
     *
     * @{inheritDoc}
     */
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    /**
     * @codeCoverageIgnore
     *
     * @{inheritDoc}
     */
    public function unserialize($serialized): void
    {
        $this->unserialize(unserialize($serialized));
    }

    /**
     * 获取 json 数据中的 code 字段
     *
     * @return int
     */
    public function code(): int
    {
        return (int) $this->offsetGet('code');
    }

    /**
     * 获取 json 数据中的 msg 字段
     *
     * @return string
     */
    public function message(): string
    {
        return (string) $this->offsetGet('message');
    }

    /**
     * 获取 meta 头数据
     *
     * @param null|string $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function meta(string $key = null, mixed $default = null): mixed
    {
        $meta = $this->offsetGet('meta') ?? [];

        return $key ? data_get($meta, $key, $default) : $meta;
    }

    /**
     * 获取 json 数据中的 data 字段
     *
     * @return array
     */
    public function data(string $key = null, mixed $default = null): mixed
    {
        $data = $this->offsetGet('data') ?? [];

        return $key ? data_get($data, $key, $default) : $data;
    }

    /**
     * 将获取到的全部响应内容转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->json();
    }

    /**
     * @{inheritDoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->json()[$offset]);
    }

    /**
     * @{inheritDoc}
     */
    public function offsetGet($offset): mixed
    {
        return $this->json()[$offset] ?? null;
    }

    /**
     * @{inheritDoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * @{inheritDoc}
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }
}
