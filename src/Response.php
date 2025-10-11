<?php

declare(strict_types=1);

namespace Mellivora\Http\Api;

use ArrayAccess;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Client\Response as HttpResponse;
use LogicException;
use Psr\Http\Message\MessageInterface;
use Serializable;

/**
 * Enhanced HTTP response wrapper with API-specific methods.
 *
 * This class extends PSR-7 Response to provide convenient methods for
 * accessing structured API response data including code, message, data,
 * and meta information following common API response patterns.
 */
class Response extends HttpResponse implements ArrayAccess, Serializable
{
    /**
     * Create a new response instance.
     *
     * @param HttpResponse|MessageInterface $response
     */
    public function __construct(HttpResponse|MessageInterface $response)
    {
        if ($response instanceof HttpResponse) {
            $response = $response->toPsrResponse();
        }

        parent::__construct($response);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    /**
     * @codeCoverageIgnore
     *
     * {@inheritDoc}
     */
    public function unserialize(string $serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    /**
     * Get the code field from json data.
     *
     * @return int
     */
    public function code(): int
    {
        return (int) $this->offsetGet('code');
    }

    /**
     * Get the message field from json data.
     *
     * @return string
     */
    public function message(): string
    {
        return (string) $this->offsetGet('message');
    }

    /**
     * Get meta header data.
     *
     * @param null|string $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function meta(string $key = null, mixed $default = null): mixed
    {
        $meta = $this->offsetGet('meta') ?? [];

        return null !== $key ? data_get($meta, $key, $default) : $meta;
    }

    /**
     * Get the data field from json data.
     *
     * @return array
     */
    public function data(string $key = null, mixed $default = null): mixed
    {
        $data = $this->offsetGet('data') ?? [];

        return null !== $key ? data_get($data, $key, $default) : $data;
    }

    /**
     * Get the timestamp from meta data.
     *
     * Attempts to parse the timestamp from the response meta data.
     * Supports numeric timestamps, string dates, and DateTimeInterface objects.
     *
     * @return DateTimeInterface|null The parsed timestamp or null if not found/parseable
     *
     * @example
     * $response->timestamp(); // Returns Carbon instance or null
     */
    public function timestamp(): ?DateTimeInterface
    {
        $timestamp = $this->meta('timestamp');

        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp($timestamp);
        }

        if (is_string($timestamp)) {
            return Carbon::parse($timestamp);
        }

        if ($timestamp instanceof DateTimeInterface) {
            return $timestamp;
        }

        return null;
    }

    /**
     * Convert all response content to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->json();
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->json()[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->json()[$offset] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }
}
