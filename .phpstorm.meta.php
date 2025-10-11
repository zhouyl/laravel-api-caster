<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    use Mellivora\Http\Api\Entity;
    use Mellivora\Http\Api\Caster;
    use Mellivora\Http\Api\Response;

    // Entity factory methods return type hints
    override(Entity::from(0), map([
        '' => '@'
    ]));

    override(Entity::collection(0), map([
        '' => '\Illuminate\Support\Collection<int, @>'
    ]));

    override(Entity::collectionResponse(0), map([
        '' => '\Illuminate\Support\Collection<int, @>'
    ]));

    // Entity attribute access
    override(Entity::getAttribute(0), map([
        'id' => 'int',
        'name' => 'string',
        'email' => 'string',
        'created_at' => '\Carbon\Carbon',
        'updated_at' => '\Carbon\Carbon',
        'settings' => 'array',
        'is_active' => 'bool',
        'score' => 'float',
        'status' => 'string',
    ]));

    override(Entity::__get(0), map([
        'id' => 'int',
        'name' => 'string',
        'email' => 'string',
        'createdAt' => '\Carbon\Carbon',
        'updatedAt' => '\Carbon\Carbon',
        'settings' => 'array',
        'isActive' => 'bool',
        'score' => 'float',
        'status' => 'string',
    ]));

    // Caster type hints
    override(Caster::cast(0), map([
        'int' => 'int',
        'integer' => 'int',
        'float' => 'float',
        'double' => 'float',
        'real' => 'float',
        'string' => 'string',
        'bool' => 'bool',
        'boolean' => 'bool',
        'array' => 'array',
        'json' => 'array',
        'object' => '\stdClass',
        'collection' => '\Illuminate\Support\Collection',
        'date' => '\Carbon\Carbon',
        'datetime' => '\Carbon\Carbon',
        'timestamp' => '\Carbon\Carbon',
    ]));

    // Response data access
    override(Response::data(0), map([
        '' => 'mixed',
        'user' => 'array',
        'users' => 'array',
        'items' => 'array',
        'total' => 'int',
        'count' => 'int',
    ]));

    override(Response::meta(0), map([
        '' => 'mixed',
        'total' => 'int',
        'page' => 'int',
        'per_page' => 'int',
        'last_page' => 'int',
        'from' => 'int',
        'to' => 'int',
    ]));

    // Array access for Entity
    override(\Mellivora\Http\Api\Entity::offsetGet(0), map([
        'id' => 'int',
        'name' => 'string',
        'email' => 'string',
        'created_at' => '\Carbon\Carbon',
        'updated_at' => '\Carbon\Carbon',
        'settings' => 'array',
        'is_active' => 'bool',
        'score' => 'float',
        'status' => 'string',
    ]));

    // Array access for Response
    override(\Mellivora\Http\Api\Response::offsetGet(0), map([
        'code' => 'int',
        'message' => 'string',
        'data' => 'array',
        'meta' => 'array',
    ]));

    // Expected arguments for cast types
    expectedArguments(\Mellivora\Http\Api\Caster::cast(), 0, 
        'int', 'integer', 'float', 'double', 'real', 'string', 'bool', 'boolean',
        'array', 'json', 'object', 'collection', 'date', 'datetime', 'timestamp',
        'decimal:0', 'decimal:1', 'decimal:2', 'decimal:3', 'decimal:4',
        'date:Y-m-d', 'date:d/m/Y', 'datetime:Y-m-d H:i:s', 'datetime:d/m/Y H:i:s'
    );

    // Expected arguments for entity casts property
    expectedArguments(\Mellivora\Http\Api\Entity::class, 'casts', 
        'int', 'integer', 'float', 'double', 'real', 'string', 'bool', 'boolean',
        'array', 'json', 'object', 'collection', 'date', 'datetime', 'timestamp',
        'decimal:0', 'decimal:1', 'decimal:2', 'decimal:3', 'decimal:4'
    );

    // Common attribute names for auto-completion
    expectedArguments(\Mellivora\Http\Api\Entity::getAttribute(), 0,
        'id', 'name', 'email', 'created_at', 'updated_at', 'deleted_at',
        'title', 'description', 'content', 'status', 'type', 'category',
        'user_id', 'parent_id', 'sort_order', 'is_active', 'is_published',
        'settings', 'metadata', 'attributes', 'properties'
    );

    expectedArguments(\Mellivora\Http\Api\Entity::setAttribute(), 0,
        'id', 'name', 'email', 'created_at', 'updated_at', 'deleted_at',
        'title', 'description', 'content', 'status', 'type', 'category',
        'user_id', 'parent_id', 'sort_order', 'is_active', 'is_published',
        'settings', 'metadata', 'attributes', 'properties'
    );

    expectedArguments(\Mellivora\Http\Api\Entity::hasAttribute(), 0,
        'id', 'name', 'email', 'created_at', 'updated_at', 'deleted_at',
        'title', 'description', 'content', 'status', 'type', 'category',
        'user_id', 'parent_id', 'sort_order', 'is_active', 'is_published',
        'settings', 'metadata', 'attributes', 'properties'
    );

    // Response method arguments
    expectedArguments(\Mellivora\Http\Api\Response::data(), 0,
        'user', 'users', 'item', 'items', 'result', 'results',
        'total', 'count', 'page', 'per_page', 'last_page'
    );

    expectedArguments(\Mellivora\Http\Api\Response::meta(), 0,
        'total', 'count', 'page', 'per_page', 'last_page', 'from', 'to',
        'current_page', 'has_more_pages', 'path', 'first_page_url', 'last_page_url',
        'next_page_url', 'prev_page_url'
    );
}
