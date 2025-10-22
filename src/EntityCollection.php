<?php

declare(strict_types=1);

namespace Mellivora\Http\Api;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use UnexpectedValueException;

/**
 * Collection class specifically for Entity instances with meta support.
 *
 * This collection extends Laravel's base Collection to provide additional
 * functionality for working with Entity instances, including meta data
 * access and type safety.
 *
 * @template TKey of array-key
 * @template TValue of Entity
 *
 * @extends BaseCollection<TKey, TValue>
 */
class EntityCollection extends BaseCollection
{
    /**
     * Create a new EntityCollection instance.
     *
     * @param iterable<Entity>     $items Collection items (must be Entity instances)
     * @param array<string, mixed> $meta  Meta information for the collection
     *
     * @throws UnexpectedValueException When items contain non-Entity instances
     */
    public function __construct(iterable $items = [], protected array $meta = [])
    {
        foreach ($items as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        parent::__construct($items);
    }

    /**
     * Push one or more entities onto the end of the collection.
     *
     * @param Entity ...$values Entities to add
     *
     * @return static
     */
    public function push(...$values): static
    {
        foreach ($values as $value) {
            $this->add($value);
        }

        return $this;
    }

    /**
     * Add an entity to the collection.
     *
     * @param Entity $item Entity to add
     *
     * @throws UnexpectedValueException When item is not an Entity instance
     *
     * @return static
     */
    public function add($item): static
    {
        if (!$item instanceof Entity) {
            throw new UnexpectedValueException('Expected instance of '.Entity::class);
        }

        return parent::add($item);
    }

    /**
     * Put an entity in the collection by key.
     *
     * @param mixed  $key
     * @param Entity $value
     *
     * @throws UnexpectedValueException When value is not an Entity instance
     *
     * @return static
     */
    public function put($key, $value): static
    {
        if (!$value instanceof Entity) {
            throw new UnexpectedValueException('Expected instance of '.Entity::class);
        }

        return parent::put($key, $value);
    }

    /**
     * Prepend an entity to the beginning of the collection.
     *
     * @param Entity $value
     * @param mixed  $key
     *
     * @throws UnexpectedValueException When value is not an Entity instance
     *
     * @return static
     */
    public function prepend($value, $key = null): static
    {
        if (!$value instanceof Entity) {
            throw new UnexpectedValueException('Expected instance of '.Entity::class);
        }

        return parent::prepend($value, $key);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param iterable $items
     *
     * @throws UnexpectedValueException When items contain non-Entity instances
     *
     * @return static
     */
    public function merge($items): static
    {
        foreach ($items as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        return parent::merge($items);
    }

    /**
     * Recursively merge the collection with the given items.
     *
     * @param iterable $items
     *
     * @throws UnexpectedValueException When items contain non-Entity instances
     *
     * @return static
     */
    public function mergeRecursive($items): static
    {
        foreach ($items as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        return parent::mergeRecursive($items);
    }

    /**
     * Concatenate values to the end of the collection.
     *
     * @param iterable $source
     *
     * @throws UnexpectedValueException When source contains non-Entity instances
     *
     * @return static
     */
    public function concat($source): static
    {
        foreach ($source as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        return parent::concat($source);
    }

    /**
     * Replace the collection items with the given items.
     *
     * @param iterable $items
     *
     * @throws UnexpectedValueException When items contain non-Entity instances
     *
     * @return static
     */
    public function replace($items): static
    {
        foreach ($items as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        return parent::replace($items);
    }

    /**
     * Recursively replace the collection items with the given items.
     *
     * @param iterable $items
     *
     * @throws UnexpectedValueException When items contain non-Entity instances
     *
     * @return static
     */
    public function replaceRecursive($items): static
    {
        foreach ($items as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        return parent::replaceRecursive($items);
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param int      $offset
     * @param null|int $length
     * @param iterable $replacement
     *
     * @throws UnexpectedValueException When replacement contains non-Entity instances
     *
     * @return static
     */
    public function splice($offset, $length = null, $replacement = []): static
    {
        foreach ($replacement as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Expected instance of '.Entity::class);
            }
        }

        return parent::splice($offset, $length, $replacement);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed  $offset
     * @param Entity $value
     *
     * @throws UnexpectedValueException When value is not an Entity instance
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Entity) {
            throw new UnexpectedValueException('Expected instance of '.Entity::class);
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * Get meta information for the collection.
     *
     * @param null|string $key     Specific meta key to retrieve
     * @param mixed       $default Default value if key not found
     *
     * @return mixed Meta value or entire meta array
     *
     * @example
     * $collection->meta(); // Get all meta
     * $collection->meta('total_items'); // Get specific meta value
     * $collection->meta('page', 1); // Get with default
     */
    public function meta(string $key = null, mixed $default = null): mixed
    {
        return null !== $key ? Arr::get($this->meta, $key, $default) : $this->meta;
    }

    /**
     * Check if the collection has specific meta information.
     *
     * @param string $key Meta key to check
     *
     * @return bool
     */
    public function hasMeta(string $key): bool
    {
        return Arr::has($this->meta, $key);
    }

    /**
     * Get pagination information from meta.
     *
     * @return array pagination info with keys: total_items, total_pages, current_page, per_page
     */
    public function pagination(): array
    {
        $pagination = [
            'total_items' => $this->meta['total_items'] ?? $this->meta['total'] ?? 0,
            'total_pages' => $this->meta['total_pages'] ?? $this->meta['last_page'] ?? 1,
            'current_page' => $this->meta['current_page'] ?? $this->meta['page'] ?? $this->meta['page_no'] ?? 1,
            'per_page' => $this->meta['per_page'] ?? $this->meta['page_size'] ?? $this->meta['limit'] ?? $this->meta['size'] ?? 10,
        ];

        $pagination['has_more'] = $pagination['current_page'] < $pagination['total_pages'];

        return $pagination;
    }

    /**
     * Check if there are more pages available.
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->pagination()['has_more'];
    }

    /**
     * Alias for totalItems().
     *
     * @return int
     */
    public function total(): int
    {
        return $this->totalItems();
    }

    /**
     * Get the total number of items across all pages.
     *
     * @return int
     */
    public function totalItems(): int
    {
        return (int) $this->pagination()['total_items'];
    }

    /**
     * Get the total number of pages.
     *
     * @return int
     */
    public function totalPages(): int
    {
        return (int) $this->pagination()['total_pages'];
    }

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function currentPage(): int
    {
        return (int) $this->pagination()['current_page'];
    }

    /**
     * Get the number of items per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        return (int) $this->pagination()['per_page'];
    }

    /**
     * Convert the collection to an array including meta information.
     *
     * @return array
     */
    public function toArrayWithMeta(): array
    {
        $data = [];

        foreach ($this->items as $entity) {
            $data[] = $entity->toArray();
        }

        return [
            'data' => $data,
            'meta' => $this->meta(),
        ];
    }

    /**
     * Convert the collection to JSON including meta information.
     *
     * @param int $options JSON encoding options
     *
     * @return string
     */
    public function toJsonWithMeta(int $options = 0): string
    {
        return json_encode($this->toArrayWithMeta(), $options);
    }

    /**
     * Run a filter over each of the items and return a new EntityCollection.
     *
     * @param null|callable $callback
     *
     * @return static
     */
    public function filter(callable $callback = null): static
    {
        $filtered = parent::filter($callback);

        return new static($filtered->all(), $this->meta);
    }

    /**
     * Run a map over each of the items and return a new EntityCollection.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function map(callable $callback): static
    {
        $mapped = parent::map($callback);

        // Validate that all mapped items are still Entity instances
        foreach ($mapped as $item) {
            if (!$item instanceof Entity) {
                throw new UnexpectedValueException('Map callback must return Entity instances');
            }
        }

        return new static($mapped->all(), $this->meta);
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param callable|mixed $callback
     *
     * @return static
     */
    public function reject($callback = true): static
    {
        $rejected = parent::reject($callback);

        return new static($rejected->all(), $this->meta);
    }

    /**
     * Reverse items order and return a new EntityCollection.
     *
     * @return static
     */
    public function reverse(): static
    {
        $reversed = parent::reverse();

        return new static($reversed->all(), $this->meta);
    }

    /**
     * Shuffle the items in the collection and return a new EntityCollection.
     *
     * @param null|int $seed
     *
     * @return static
     */
    public function shuffle($seed = null): static
    {
        $shuffled = $seed !== null ? parent::shuffle($seed) : parent::shuffle();

        return new static($shuffled->all(), $this->meta);
    }

    /**
     * Sort through each item with a callback and return a new EntityCollection.
     *
     * @param null|callable $callback
     *
     * @return static
     */
    public function sort($callback = null): static
    {
        $sorted = parent::sort($callback);

        return new static($sorted->all(), $this->meta);
    }

    /**
     * Sort the collection using the given callback and return a new EntityCollection.
     *
     * @param callable|string $callback
     * @param int             $options
     * @param bool            $descending
     *
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false): static
    {
        $sorted = parent::sortBy($callback, $options, $descending);

        return new static($sorted->all(), $this->meta);
    }

    /**
     * Sort the collection in descending order using the given callback and return a new EntityCollection.
     *
     * @param callable|string $callback
     * @param int             $options
     *
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR): static
    {
        $sorted = parent::sortByDesc($callback, $options);

        return new static($sorted->all(), $this->meta);
    }

    /**
     * Take the first or last {$limit} items and return a new EntityCollection.
     *
     * @param int $limit
     *
     * @return static
     */
    public function take($limit): static
    {
        $taken = parent::take($limit);

        return new static($taken->all(), $this->meta);
    }

    /**
     * Get a slice of the underlying collection array and return a new EntityCollection.
     *
     * @param int      $offset
     * @param null|int $length
     *
     * @return static
     */
    public function slice($offset, $length = null): static
    {
        $sliced = parent::slice($offset, $length);

        return new static($sliced->all(), $this->meta);
    }

    /**
     * Get the items with the specified keys and return a new EntityCollection.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function only($keys): static
    {
        $filtered = parent::only($keys);

        return new static($filtered->all(), $this->meta);
    }

    /**
     * Get all items except for those with the specified keys and return a new EntityCollection.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function except($keys): static
    {
        $filtered = parent::except($keys);

        return new static($filtered->all(), $this->meta);
    }
}
