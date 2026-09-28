<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit;

use Elasticsearch\Client as ElasticsearchClient;
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\MultiSearchRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

final class ClientTest extends TestCase
{
    public function testWrapsTheInjectedClient(): void
    {
        $elastic = $this->createMock(ElasticsearchClient::class);
        $elastic->expects(self::once())
            ->method('search')
            ->with(['index' => 'kyc', 'body' => ['query' => ['term' => ['a' => ['value' => 1]]], 'size' => 10, 'from' => 0]])
            ->willReturn(['hits' => ['total' => ['value' => 1, 'relation' => 'eq'], 'hits' => [['_index' => 'kyc', '_id' => 'k1', '_score' => 2.0]]]]);

        $client = new Client($elastic);
        $response = $client->search((new SearchRequest('kyc'))->source((new SearchSourceBuilder())->query(QueryBuilders::termQuery('a', 1))));

        self::assertSame('k1', $response->hits()[0]->id);
        self::assertSame($elastic, $client->elasticsearch());
    }

    public function testEmptyMultiSearchIsNotSent(): void
    {
        $elastic = $this->createMock(ElasticsearchClient::class);
        $elastic->expects(self::never())->method('msearch');

        $response = (new Client($elastic))->msearch(new MultiSearchRequest());
        self::assertCount(0, $response);
        self::assertSame([], $response->all());
    }

    public function testTwoClientsForDifferentClustersAreIndependent(): void
    {
        $first = Client::create(['http://first:9200']);
        $second = Client::create(['http://second:9200']);

        self::assertNotSame($first->elasticsearch(), $second->elasticsearch());
    }
}
