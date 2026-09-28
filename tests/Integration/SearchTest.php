<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Integration;

use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\MultiSearchRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;
use Zvonchuk\Elastic\Query\MultiMatchQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\MultiSearchException;
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

        $client = $this->client();

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
        $response = $this->client()
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
        $response = $this->client()
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
        $response = $this->client()
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
        $response = $this->client()
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
        $response = $this->client()
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
        $response = $this->client()
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['mid'], array_column($response->getHits(), '_id'));
    }

    public function testTermOnABooleanField(): void
    {
        $this->seed([
            'on' => ['active' => true],
            'off' => ['active' => false],
        ], ['active' => ['type' => 'boolean']]);

        $client = $this->client();
        $find = fn (bool $value) => array_column($client->search((new SearchRequest($this->index))->source(
            (new SearchSourceBuilder())->query(QueryBuilders::termQuery('active', $value)),
        ))->getHits(), '_id');

        self::assertSame(['on'], $find(true));
        self::assertSame(['off'], $find(false));
    }

    public function testDocumentsWithGeneratedIdsAndCountWithoutQuery(): void
    {
        $this->seed([]);
        $client = $this->client();

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

        $response = $this->client()->search((new SearchRequest($this->index))
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

        $response = $this->client()
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
        $client = $this->client();
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
        $response = $this->client()
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['both'], array_column($response->getHits(), '_id'));
    }

    public function testFuzzyMatchPrefixLength(): void
    {
        $this->seed(['1' => ['name' => 'huseynov']], ['name' => ['type' => 'text']]);
        $client = $this->client();
        $hits = fn (int $prefixLength) => count($client->search((new SearchRequest($this->index))->source(
            (new SearchSourceBuilder())->query(QueryBuilders::matchQuery('name', 'guseynov')->fuzziness(1)->prefixLength($prefixLength)),
        ))->getHits());

        self::assertSame(1, $hits(0), 'a typo in the first letter is found when no prefix has to match');
        self::assertSame(0, $hits(1), 'and missed when the first letter has to match exactly');
    }

    public function testMultiMatchAcrossFields(): void
    {
        $this->seed([
            'in_name' => ['name' => 'iphone 15', 'description' => 'phone'],
            'in_description' => ['name' => 'case', 'description' => 'fits iphone 15'],
            'other' => ['name' => 'laptop', 'description' => 'computer'],
        ]);

        $query = QueryBuilders::multiMatchQuery('iphone', ['name^3', 'description'])->type(MultiMatchQueryBuilder::BEST_FIELDS);
        $response = $this->client()
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['in_name', 'in_description'], array_column($response->getHits(), '_id'));
    }

    public function testDisMaxAndConstantScore(): void
    {
        $this->seed(['1' => ['tag' => 'a', 'year' => '1950']], ['tag' => ['type' => 'keyword'], 'year' => ['type' => 'keyword']]);
        $client = $this->client();
        $score = fn ($query) => $client->search((new SearchRequest($this->index))->source(
            (new SearchSourceBuilder())->query($query),
        ))->getHits()[0]['_score'];

        $constant = fn (float $boost) => QueryBuilders::constantScoreQuery(QueryBuilders::termQuery('tag', 'a'))->boost($boost);
        self::assertEqualsWithDelta(3.0, $score($constant(3)), 0.0001);

        $disMax = QueryBuilders::disMaxQuery()->add($constant(3))->add($constant(5));
        self::assertEqualsWithDelta(5.0, $score($disMax), 0.0001, 'dis_max takes the best query');
        self::assertEqualsWithDelta(5.3, $score($disMax->tieBreaker(0.1)), 0.0001, 'plus tie_breaker x the others');
    }

    public function testTermLevelQueries(): void
    {
        $this->seed([
            '1' => ['code' => 'AZE1234567', 'name' => 'huseynov'],
            '2' => ['code' => 'GEO7654321', 'name' => 'aliyev'],
        ], ['code' => ['type' => 'keyword'], 'name' => ['type' => 'keyword']]);
        $client = $this->client();
        $ids = fn ($query) => array_column($client->search((new SearchRequest($this->index))->source(
            (new SearchSourceBuilder())->query($query),
        ))->getHits(), '_id');

        self::assertSame(['1'], $ids(QueryBuilders::prefixQuery('code', 'aze')->caseInsensitive()));
        self::assertSame(['2'], $ids(QueryBuilders::wildcardQuery('code', 'GEO*21')));
        self::assertSame(['2'], $ids(QueryBuilders::idsQuery(['2', 'missing'])));
        self::assertSame(['1'], $ids(QueryBuilders::fuzzyQuery('name', 'guseynov')->fuzziness(1)->prefixLength(0)));
    }

    public function testNestedMatchesEachObjectOnItsOwn(): void
    {
        $this->seed([
            'match' => ['documents' => [['type' => 'passport', 'number' => 'A1'], ['type' => 'id', 'number' => 'B2']]],
            'crossed' => ['documents' => [['type' => 'passport', 'number' => 'B2'], ['type' => 'id', 'number' => 'A1']]],
        ], ['documents' => ['type' => 'nested', 'properties' => [
            'type' => ['type' => 'keyword'], 'number' => ['type' => 'keyword'],
        ]]]);

        $query = QueryBuilders::nestedQuery('documents', QueryBuilders::boolQuery()
            ->must(QueryBuilders::termQuery('documents.type', 'passport'))
            ->must(QueryBuilders::termQuery('documents.number', 'A1')));
        $response = $this->client()
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(['match'], array_column($response->getHits(), '_id'));
    }

    public function testTimeoutTotalHitsAndMinScore(): void
    {
        $this->seed(['strong' => ['tag' => 'a'], 'weak' => ['tag' => 'b']], ['tag' => ['type' => 'keyword']]);
        $query = QueryBuilders::boolQuery()
            ->should(QueryBuilders::constantScoreQuery(QueryBuilders::termQuery('tag', 'a'))->boost(5))
            ->should(QueryBuilders::constantScoreQuery(QueryBuilders::termQuery('tag', 'b'))->boost(1));

        $response = $this->client()->search((new SearchRequest($this->index))
            ->source((new SearchSourceBuilder())->query($query)->timeout('2s')->trackTotalHits(false)->minScore(2.0)));

        self::assertSame(['strong'], array_column($response->getHits(), '_id'));
        self::assertNull($response->getTotal());
    }

    public function testTypedHitsCarryMatchedQueries(): void
    {
        $this->seed(['1' => ['tag' => 'a', 'n' => 5]], ['tag' => ['type' => 'keyword'], 'n' => ['type' => 'integer']]);
        $query = QueryBuilders::boolQuery()
            ->should(QueryBuilders::termQuery('tag', 'a')->queryName('tag'))
            ->should(QueryBuilders::rangeQuery('n')->gte(10)->queryName('big'));

        $response = $this->client()
            ->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        $hit = $response->hits()[0];
        self::assertSame('1', $hit->id);
        self::assertSame($this->index, $hit->index);
        self::assertSame(['tag' => 'a', 'n' => 5], $hit->source);
        self::assertTrue($hit->matched('tag'));
        self::assertFalse($hit->matched('big'));
        self::assertFalse($response->timedOut());
        self::assertIsInt($response->took());
    }

    public function testDocumentsFromARealResponse(): void
    {
        $this->seed(['1' => ['full_name' => 'Jahangir Asgarov', 'birth_year' => 1950]], ['birth_year' => ['type' => 'integer']]);

        $people = $this->client()
            ->search(new SearchRequest($this->index))
            ->documents(Person::class);

        self::assertEquals([new Person('Jahangir Asgarov', 1950)], $people);
    }

    public function testMultiSearchInOneRoundTrip(): void
    {
        $this->seed(['1' => ['tag' => 'a'], '2' => ['tag' => 'b']], ['tag' => ['type' => 'keyword']]);
        $search = fn (string $index, string $tag) => (new SearchRequest($index))
            ->source((new SearchSourceBuilder())->query(QueryBuilders::termQuery('tag', $tag)));
        $client = $this->client();

        $response = $client->msearch((new MultiSearchRequest())->add($search($this->index, 'a'))->add($search($this->index, 'b')));
        self::assertSame(['1'], array_column($response->get(0)->getHits(), '_id'));
        self::assertSame(['2'], array_column($response->get(1)->getHits(), '_id'));

        $withMissingIndex = $client->msearch((new MultiSearchRequest())
            ->add($search($this->index, 'a'))
            ->add($search($this->index . '_missing', 'a')));
        self::assertSame([1], array_keys($withMissingIndex->failures()));
        $this->expectException(MultiSearchException::class);
        $withMissingIndex->all();
    }

    public function testReindexBehindAnAliasWithoutDowntime(): void
    {
        $indices = $this->client()->indices();
        $alias = $this->index;
        $v1 = $this->index . '_v1';
        $v2 = $this->index . '_v2';
        $mappings = ['dynamic' => 'strict', 'properties' => ['tag' => ['type' => 'keyword']]];

        $indices->create((new CreateRequest($v1))->mappings($mappings)->alias($alias));
        self::$elasticsearch->index(['index' => $v1, 'id' => 'old', 'body' => ['tag' => 'a'], 'refresh' => true]);
        $indices->create((new CreateRequest($v2))->mappings($mappings));
        self::$elasticsearch->index(['index' => $v2, 'id' => 'new', 'body' => ['tag' => 'a'], 'refresh' => true]);

        $read = fn () => array_column($this->client()->search(new SearchRequest($alias))->getHits(), '_id');
        self::assertSame([$v1], $indices->indicesForAlias($alias));
        self::assertSame(['old'], $read());

        self::assertSame([$v1], $indices->swapAlias($alias, $v2));
        self::assertSame([$v2], $indices->indicesForAlias($alias));
        self::assertSame(['new'], $read());
        self::assertSame([], $indices->swapAlias($alias, $v2), 'swapping to the current index changes nothing');
        self::assertSame([], $indices->indicesForAlias($alias . '_none'));
    }
}

final class Person
{
    public function __construct(public readonly string $fullName, public readonly int $birthYear)
    {
    }
}
