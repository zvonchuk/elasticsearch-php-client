<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Core;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;
use Zvonchuk\Elastic\Core\ExistsRequest;
use Zvonchuk\Elastic\Core\GetRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\Request;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class OptionalPartsTest extends TestCase
{
    public function testIndexWithoutIdLetsElasticsearchGenerateIt(): void
    {
        self::assertRenders('{"index":"p","body":{"a":1}}', (new IndexRequest('p'))->source(['a' => 1])->getSource());
    }

    public function testBulkIndexWithoutId(): void
    {
        $bulk = (new BulkRequest())->add((new IndexRequest('p'))->source(['a' => 1]));
        self::assertRenders('{"body":[{"index":{"_index":"p"}},{"a":1}]}', $bulk->getSource());
    }

    public function testEmptyBulk(): void
    {
        $bulk = new BulkRequest();
        self::assertTrue($bulk->isEmpty());
        self::assertRenders('{"body":[]}', $bulk->getSource());
        self::assertFalse($bulk->add((new DeleteRequest('p'))->id('1'))->isEmpty());
    }

    public function testCountWithoutQueryCountsEverything(): void
    {
        self::assertRenders('{"index":"p"}', (new CountRequest('p'))->getSource());
    }

    public function testSearchWithoutSourceUsesDefaults(): void
    {
        self::assertRenders('{"index":"p","body":{"size":10,"from":0}}', (new SearchRequest('p'))->getSource());
    }

    /** @return iterable<string, array{Request}> */
    public static function requestsThatNeedAnId(): iterable
    {
        yield 'update' => [(new UpdateRequest('p'))->source(['a' => 1])];
        yield 'delete' => [new DeleteRequest('p')];
        yield 'get' => [new GetRequest('p')];
        yield 'exists' => [new ExistsRequest('p')];
    }

    #[DataProvider('requestsThatNeedAnId')]
    public function testMissingIdIsExplained(Request $request): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('document id');
        $request->getSource();
    }

    public function testIntegerIdsAreAccepted(): void
    {
        self::assertRenders('{"index":"p","id":"42"}', (new GetRequest('p'))->id(42)->toArray());
        self::assertRenders('{"index":"p","id":"42","body":{"a":1}}', (new IndexRequest('p'))->id(42)->source(['a' => 1])->toArray());
    }
}
