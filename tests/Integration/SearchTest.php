<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Integration;

use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

final class SearchTest extends IntegrationTestCase
{
    public function testSearchAndCountThroughTheClient(): void
    {
        $this->seed([
            '1' => ['title' => 'elasticsearch in action', 'status' => 'active'],
            '2' => ['title' => 'php in action', 'status' => 'draft'],
        ], ['status' => ['type' => 'keyword']]);

        $client = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')]);

        $query = QueryBuilders::boolQuery()
            ->must(QueryBuilders::matchQuery('title', 'action'))
            ->filter(QueryBuilders::termQuery('status', 'active'));
        $response = $client->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(1, $response->getTotal());
        self::assertSame('1', $response->getHits()[0]['_id']);
        self::assertSame(2, $client->count((new CountRequest($this->index))->query(QueryBuilders::matchAllQuery()))->getCount());
    }

    public function testFilterAggregationRunsOnTheCluster(): void
    {
        $this->seed([
            '1' => ['status' => 'active', 'price' => 10],
            '2' => ['status' => 'active', 'price' => 30],
            '3' => ['status' => 'draft', 'price' => 99],
        ], ['status' => ['type' => 'keyword'], 'price' => ['type' => 'integer']]);

        $agg = AggregationBuilders::filter('active', QueryBuilders::termQuery('status', 'active'))
            ->subAggregation(AggregationBuilders::avg('avg_price')->field('price'));
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->size(0)->aggregation($agg)));

        self::assertSame(2, $response->getAggregations()['active']['doc_count']);
        self::assertEqualsWithDelta(20.0, $response->getAggregations()['active']['avg_price']['value'], 0.001);
    }

    public function testTermsKeepsSubAggregationsOnTheCluster(): void
    {
        $this->seed([
            '1' => ['tag' => 'a', 'price' => 10],
            '2' => ['tag' => 'a', 'price' => 20],
            '3' => ['tag' => 'b', 'price' => 50],
        ], ['tag' => ['type' => 'keyword'], 'price' => ['type' => 'integer']]);

        $agg = AggregationBuilders::terms('by_tag')->field('tag')
            ->subAggregation(AggregationBuilders::avg('avg_price')->field('price'));
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->size(0)->aggregation($agg)));

        $buckets = array_column($response->getAggregations()['by_tag']['buckets'], null, 'key');
        self::assertEqualsWithDelta(15.0, $buckets['a']['avg_price']['value'], 0.001);
        self::assertEqualsWithDelta(50.0, $buckets['b']['avg_price']['value'], 0.001);
    }
}
