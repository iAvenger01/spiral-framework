<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Options;
use Spiral\Queue\Queue;

final class QueueTest extends TestCase
{
    public static function pushDataProvider(): \Traversable
    {
        yield ['some string', new Options()];
        yield [123, new Options()];
        yield [['baz' => 'baf'], new Options()];
        yield [new \stdClass(), new Options()];
        yield [new \stdClass(), new \stdClass()];
    }

    #[DataProvider('pushDataProvider')]
    public function testPush(mixed $payload, mixed $options): void
    {
        $queue = new Queue(
            $core = m::mock(CoreInterface::class),
        );

        $core->shouldReceive('callAction')->once()
            ->with('foo', 'push', [
                'payload' => $payload,
                'options' => $options,
            ])
            ->andReturn('task-id');

        $id = $queue->push('foo', $payload, $options);

        self::assertSame('task-id', $id);
    }
}
