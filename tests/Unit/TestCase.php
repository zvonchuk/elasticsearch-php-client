<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Compares what would be sent to Elasticsearch: the JSON encoding, so that
     * `new \stdClass()` and `[]` are told apart and key order matters.
     */
    protected static function assertRenders(string $expectedJson, mixed $actual): void
    {
        self::assertJsonStringEqualsJsonString($expectedJson, (string) json_encode($actual));
        self::assertSame(
            json_encode(json_decode($expectedJson)),
            json_encode($actual),
            'Rendered JSON differs in key order or empty-object encoding',
        );
    }
}
