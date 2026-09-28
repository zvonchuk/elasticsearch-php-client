<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Core;

use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;
use Zvonchuk\Elastic\Core\ExistsRequest;
use Zvonchuk\Elastic\Core\GetRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;
use Zvonchuk\Elastic\Indices\DeleteRequest as DeleteIndexRequest;
use Zvonchuk\Elastic\Indices\GetMappingsRequest;
use Zvonchuk\Elastic\Indices\IndexRequest as IndexExistsRequest;
use Zvonchuk\Elastic\Indices\PutMappingsRequest;
use Zvonchuk\Elastic\Indices\RefreshRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class RequestsTest extends TestCase
{
    public function testSearch(): void
    {
        $request = (new SearchRequest('products'))->source((new SearchSourceBuilder())->size(1));
        self::assertRenders('{"index":"products","body":{"size":1,"from":0}}', $request->getSource());
    }

    public function testCount(): void
    {
        $request = (new CountRequest('products'))->query(QueryBuilders::termQuery('status', 'active'));
        self::assertRenders('{"index":"products","body":{"query":{"term":{"status":{"value":"active"}}}}}', $request->getSource());
    }

    public function testDocumentRequests(): void
    {
        self::assertRenders('{"index":"p","id":"1","body":{"a":1}}', (new IndexRequest('p'))->id('1')->source(['a' => 1])->getSource());
        self::assertRenders('{"index":"p","id":"1","body":{"doc":{"a":2}}}', (new UpdateRequest('p'))->id('1')->source(['a' => 2])->getSource());
        self::assertRenders('{"index":"p","id":"1"}', (new DeleteRequest('p'))->id('1')->getSource());
        self::assertRenders('{"index":"p","id":"1"}', (new GetRequest('p'))->id('1')->getSource());
        self::assertRenders('{"index":"p","id":"1"}', (new ExistsRequest('p'))->id('1')->getSource());
    }

    public function testBulk(): void
    {
        $bulk = (new BulkRequest())
            ->add((new IndexRequest('p'))->id('1')->source(['a' => 1]))
            ->add((new UpdateRequest('p'))->id('2')->source(['a' => 2]))
            ->add((new DeleteRequest('p'))->id('3'));

        self::assertRenders(
            '{"body":[{"index":{"_index":"p","_id":"1"}},{"a":1},{"update":{"_index":"p","_id":"2"}},{"doc":{"a":2}},'
            . '{"delete":{"_index":"p","_id":"3"}}]}',
            $bulk->getSource(),
        );
    }

    public function testIndicesRequests(): void
    {
        self::assertRenders(
            '{"index":"p","body":{"settings":{"number_of_shards":1}}}',
            (new CreateRequest('p'))->settings(['number_of_shards' => 1])->getSource(),
        );
        self::assertRenders(
            '{"index":"p","body":{"properties":{"title":{"type":"text"}}}}',
            (new PutMappingsRequest('p'))->properties(['title' => ['type' => 'text']])->getSource(),
        );
        self::assertRenders('{"index":"p"}', (new DeleteIndexRequest('p'))->getSource());
        self::assertRenders('{"index":"p"}', (new RefreshRequest('p'))->getSource());
        self::assertRenders('{"index":"p"}', (new GetMappingsRequest('p'))->getSource());
        self::assertRenders('{"index":"p"}', (new IndexExistsRequest('p'))->getSource());
    }
}
