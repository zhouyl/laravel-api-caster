<?php

namespace Mellivora\Http\Api\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Exceptions\MathException;
use Mellivora\Http\Api\Caster;
use Mellivora\Http\Api\Tests\MockLib\Message;
use Mellivora\Http\Api\Tests\MockLib\SplitterCastAttribute;
use Mellivora\Http\Api\Tests\MockLib\StatusEnum;
use stdClass;

class CasterTest extends TestCase
{
    public function testCast(): void
    {
        $random = md5(microtime(true));

        $objectArray = ['foo' => $random];
        $objectJson  = '{"foo":"'.$random.'"}';
        $listJson    = "[$objectJson]";
        $listArray   = [$objectArray];

        $casts = [
            'int'                => 'int',
            'string'             => 'string',
            'bool'               => 'bool',
            'float'              => 'float',
            'decimal'            => 'decimal',
            'decimal_f'          => 'decimal:5',
            'object'             => 'object',
            'json'               => 'json',
            'array'              => 'array',
            'collection'         => 'collection',
            'date'               => 'date',
            'date_f'             => 'date:Y-m-d',
            'datetime'           => 'datetime',
            'datetime_f'         => 'datetime:Y-m-d H:i:s',
            'immutable_date'     => 'immutable_date:Y-m-d',
            'immutable_datetime' => 'immutable_datetime:Y-m-d H:i:s',
            'timestamp'          => 'timestamp',
            'path'               => SplitterCastAttribute::class.':12345',
            'status'             => StatusEnum::class,
            'message'            => Message::class,
            'not_castable'       => 'NotExistsClass',
        ];

        $caster = new Caster();

        $this->assertSame($caster->cast($casts['int'], '123'), 123);
        $this->assertSame($caster->value($casts['int'], $caster->cast($casts['int'], '123')), 123);

        $this->assertSame($caster->cast($casts['string'], 123), '123');
        $this->assertSame($caster->value($casts['string'], $caster->cast($casts['string'], 123)), '123');

        $this->assertSame($caster->cast($casts['bool'], 1), true);
        $this->assertSame($caster->value($casts['bool'], $caster->cast($casts['bool'], 1)), true);

        $this->assertSame($caster->cast($casts['float'], '1.234'), 1.234);
        $this->assertSame($caster->value($casts['float'], $caster->cast($casts['float'], '1.234')), 1.234);

        $this->assertSame($caster->cast($casts['decimal'], '1.234'), '1');
        $this->assertSame($caster->value($casts['decimal'], $caster->cast($casts['decimal'], '1.234')), '1');

        $this->assertSame($caster->cast($casts['decimal_f'], '1.234'), '1.23400');
        $this->assertSame($caster->value($casts['decimal_f'], $caster->cast($casts['decimal_f'], '1.234')), '1.23400');

        $object = $caster->cast($casts['object'], $objectJson);
        $this->assertInstanceOf(stdClass::class, $object);
        $this->assertSame($object->foo, $random);
        $this->assertSame($caster->value($casts['object'], $object), $objectArray);

        $array = $caster->cast($casts['array'], $objectJson);
        $this->assertIsArray($array);
        $this->assertSame($array['foo'], $random);
        $this->assertSame($caster->value($casts['array'], $array), $objectArray);

        $json = $caster->cast($casts['json'], $objectJson);
        $this->assertIsArray($json);
        $this->assertSame($json['foo'], $random);
        $this->assertSame($caster->value($casts['json'], $json), $objectArray);

        $collection = $caster->cast($casts['collection'], $listJson);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame($collection->first()['foo'], $random);
        $this->assertSame($caster->value($casts['collection'], $collection), $listArray);

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['date'], '2024/1/1'));
        $this->assertSame($caster->value($casts['date'], '2024-1-1'), '2024-01-01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['date'], new DateTime('2024-01-01')));
        $this->assertSame($caster->value($casts['date'], new DateTime('2024-01-01')), '2024-01-01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['date'], new DateTimeImmutable('2024-01-01')));
        $this->assertSame($caster->value($casts['date'], new DateTimeImmutable('2024-01-01')), '2024-01-01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['date_f'], '2024/1/1'));
        $this->assertSame($caster->value($casts['date_f'], '2024-1-1'), '2024-01-01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['datetime'], '2024/1/1 1:1:1'));
        $this->assertSame($caster->value($casts['datetime'], '2024/1/1 1:1:1'), '2024-01-01 01:01:01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['datetime_f'], '2024/1/1 1:1:1'));
        $this->assertSame($caster->value($casts['datetime_f'], '2024/1/1 1:1:1'), '2024-01-01 01:01:01');

        $this->assertInstanceOf(CarbonImmutable::class, $caster->cast($casts['immutable_date'], '2024/1/1'));
        $this->assertSame($caster->value($casts['immutable_date'], '2024-1-1'), '2024-01-01');

        $this->assertInstanceOf(CarbonImmutable::class, $caster->cast($casts['immutable_datetime'], '2024/1/1 1:1:1'));
        $this->assertSame($caster->value($casts['immutable_datetime'], '2024/1/1 1:1:1'), '2024-01-01 01:01:01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['datetime'], new DateTime('2024-01-01 01:01:01')));
        $this->assertSame($caster->value($casts['datetime'], new DateTime('2024-01-01 01:01:01')), '2024-01-01 01:01:01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['datetime'], new DateTimeImmutable('2024-01-01 01:01:01')));
        $this->assertSame($caster->value($casts['datetime'], new DateTime('2024-01-01 01:01:01')), '2024-01-01 01:01:01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['datetime'], strtotime('2024-01-01 01:01:01')));
        $this->assertSame($caster->value($casts['datetime'], '2024/1/1 1:1:1'), '2024-01-01 01:01:01');

        $this->assertInstanceOf(Carbon::class, $caster->cast($casts['datetime'], Carbon::make('2024-01-01 01:01:01')));
        $this->assertSame($caster->value($casts['datetime'], '2024/1/1 1:1:1'), '2024-01-01 01:01:01');

        $this->assertIsInt($caster->cast($casts['timestamp'], '2024/1/1 1:1:1'));
        $this->assertSame($caster->value($casts['timestamp'], '2024/1/1 1:1:1'), strtotime('2024-01-01 01:01:01'));

        $this->assertSame($caster->cast($casts['path'], '0,12,1223,445,'), [0, 12, 1223, 445]);
        $this->assertSame($caster->value($casts['path'], $caster->cast($casts['path'], '0,12,1223,445,')), '0,12,1223,445');
        $this->assertNull($caster->cast($casts['path'], null));
        $this->assertNull($caster->value($casts['path'], null));

        $this->assertSame($caster->cast($casts['status'], '1'), StatusEnum::ONE);
        $this->assertSame($caster->value($casts['status'], $caster->cast($casts['status'], '1')), 1);
        $this->assertNull($caster->cast($casts['status'], null));
        $this->assertNull($caster->value($casts['status'], null));

        $this->assertSame($caster->cast($casts['message'], '123.45'), [Message::class => '123.45']);
        $this->assertSame($caster->value($casts['message'], [Message::class => '123.45']), '123.45');
        $this->assertNull($caster->cast($casts['message'], null));
        $this->assertNull($caster->value($casts['message'], null));

        $this->assertSame($caster->cast($casts['not_castable'], '123.45'), '123.45');
        $this->assertSame($caster->value($casts['not_castable'], '123.45'), '123.45');

    }

    public function testMathException()
    {
        $caster = new Caster();
        $this->expectException(MathException::class);
        $this->assertSame($caster->cast('decimal:5', '1.234abc'), '1.23400');
    }
}
