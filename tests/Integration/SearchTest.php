<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Integration;

use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\GeoSort;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

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

    public function testGeoDistanceSortOnACustomField(): void
    {
        $this->seed([
            'far' => ['pin' => ['lat' => 41.0, 'lon' => 50.0]],
            'near' => ['pin' => ['lat' => 40.41, 'lon' => 49.87]],
        ], ['pin' => ['type' => 'geo_point']]);

        $sort = SortBuilders::geoDistanceSort('pin', 40.4, 49.86)->order(SortBuilder::ASC)->unit(GeoSort::NAUTICALMILES);
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->sort($sort)));

        self::assertSame(['near', 'far'], array_column($response->getHits(), '_id'));
    }

    public function testGeoDistanceQueryOnACustomField(): void
    {
        $this->seed([
            'far' => ['pin' => ['lat' => 41.0, 'lon' => 50.0]],
            'near' => ['pin' => ['lat' => 40.41, 'lon' => 49.87]],
        ], ['pin' => ['type' => 'geo_point']]);

        $query = QueryBuilders::geoDistanceQuery('pin')->distance('5km')->point(40.4, 49.86);
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['near'], array_column($response->getHits(), '_id'));
    }

    public function testMustNotExcludesDocuments(): void
    {
        $this->seed([
            '1' => ['status' => 'active'],
            '2' => ['status' => 'deleted'],
        ], ['status' => ['type' => 'keyword']]);

        $query = QueryBuilders::boolQuery()->mustNot(QueryBuilders::termQuery('status', 'deleted'));
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['1'], array_column($response->getHits(), '_id'));
    }

    public function testRangeWithNumericAndDateBounds(): void
    {
        $this->seed([
            'cheap' => ['price' => 100, 'created_at' => '2026-03-01T10:00:00+04:00'],
            'mid' => ['price' => 900.5, 'created_at' => '2026-06-01T10:00:00+04:00'],
            'old' => ['price' => 950, 'created_at' => '2025-01-01T10:00:00+04:00'],
        ], ['price' => ['type' => 'float'], 'created_at' => ['type' => 'date']]);

        $query = QueryBuilders::boolQuery()
            ->filter(QueryBuilders::rangeQuery('price')->gte(500)->lte(1000.0))
            ->filter(QueryBuilders::rangeQuery('created_at')->gte(new \DateTimeImmutable('2026-01-01T00:00:00+04:00')));
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['mid'], array_column($response->getHits(), '_id'));
    }

    public function testTermOnABooleanField(): void
    {
        $this->seed([
            'on' => ['active' => true],
            'off' => ['active' => false],
        ], ['active' => ['type' => 'boolean']]);

        $client = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')]);
        $find = fn (bool $value) => array_column($client->search((new SearchRequest($this->index))->source(
            (new SearchSourceBuilder())->query(QueryBuilders::termQuery('active', $value)),
        ))->getHits(), '_id');

        self::assertSame(['on'], $find(true));
        self::assertSame(['off'], $find(false));
    }

    public function testDocumentsWithGeneratedIdsAndCountWithoutQuery(): void
    {
        $this->seed([]);
        $client = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')]);

        $single = $client->index((new IndexRequest($this->index))->source(['title' => 'one']));
        $bulk = $client->bulk((new BulkRequest())
            ->add((new IndexRequest($this->index))->source(['title' => 'two']))
            ->add((new IndexRequest($this->index))->source(['title' => 'three'])));
        self::$elasticsearch->indices()->refresh(['index' => $this->index]);

        self::assertNotEmpty($single['_id']);
        self::assertFalse($bulk['errors']);
        self::assertSame(3, $client->count(new CountRequest($this->index))->getCount());
    }

    public function testPercentilesDefaultToTheElasticsearchSet(): void
    {
        $this->seed(['1' => ['took' => 5], '2' => ['took' => 50]], ['took' => ['type' => 'integer']]);

        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])->search((new SearchRequest($this->index))
            ->source((new SearchSourceBuilder())->size(0)->aggregation(AggregationBuilders::percentiles('p')->field('took'))));

        self::assertSame(['1.0', '5.0', '25.0', '50.0', '75.0', '95.0', '99.0'], array_keys($response->getAggregations()['p']['values']));
    }

    public function testEveryQueryTypeAcceptsBoostAndName(): void
    {
        $this->seed(
            ['1' => ['title' => 'quick brown fox', 'tag' => 'animal', 'n' => 5, 'pin' => ['lat' => 40.41, 'lon' => 49.87]]],
            ['tag' => ['type' => 'keyword'], 'n' => ['type' => 'integer'], 'pin' => ['type' => 'geo_point']],
        );

        $named = [
            'match' => QueryBuilders::matchQuery('title', 'fox'),
            'term' => QueryBuilders::termQuery('tag', 'animal'),
            'terms' => QueryBuilders::termsQuery('tag', ['animal', 'plant']),
            'range' => QueryBuilders::rangeQuery('n')->gte(1)->lte(10),
            'match_phrase' => QueryBuilders::matchPhraseQuery('title', 'brown fox'),
            'match_phrase_prefix' => QueryBuilders::matchPhrasePrefixQuery('title', 'quick bro'),
            'exists' => QueryBuilders::existsQuery('title'),
            'match_all' => QueryBuilders::matchAllQuery(),
            'geo_distance' => QueryBuilders::geoDistanceQuery('pin')->distance('5km')->point(40.4, 49.86),
            'geo_bounding_box' => QueryBuilders::geoBoundingBoxQuery('pin')
                ->topLeft(['lat' => 41.0, 'lon' => 49.0])->bottomRight(['lat' => 40.0, 'lon' => 50.0]),
        ];
        $bool = QueryBuilders::boolQuery()->queryName('bool')->boost(1.5);
        foreach ($named as $name => $query) {
            $bool->should($query->queryName($name)->boost(2));
        }

        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($bool)));

        $matched = $response->getHits()[0]['matched_queries'];
        sort($matched);
        $expected = array_merge(array_keys($named), ['bool']);
        sort($expected);
        self::assertSame($expected, $matched);
    }

    public function testBoostChangesTheOrder(): void
    {
        $this->seed(['a' => ['tag' => 'a'], 'b' => ['tag' => 'b']], ['tag' => ['type' => 'keyword']]);
        $client = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')]);
        $order = fn (float $boostA, float $boostB) => array_column($client->search((new SearchRequest($this->index))->source(
            (new SearchSourceBuilder())->query(QueryBuilders::boolQuery()
                ->should(QueryBuilders::termQuery('tag', 'a')->boost($boostA))
                ->should(QueryBuilders::termQuery('tag', 'b')->boost($boostB))),
        ))->getHits(), '_id');

        self::assertSame(['a', 'b'], $order(5, 1));
        self::assertSame(['b', 'a'], $order(1, 5));
    }

    public function testMinimumShouldMatch(): void
    {
        $this->seed([
            'both' => ['a' => 'x', 'b' => 'y'],
            'one' => ['a' => 'x', 'b' => 'z'],
        ], ['a' => ['type' => 'keyword'], 'b' => ['type' => 'keyword']]);

        $query = QueryBuilders::boolQuery()
            ->should(QueryBuilders::termQuery('a', 'x'))
            ->should(QueryBuilders::termQuery('b', 'y'))
            ->minimumShouldMatch(2);
        $response = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')])
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['both'], array_column($response->getHits(), '_id'));
    }
}
