<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use Zvonchuk\Elastic\Query\QueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

/**
 * boost and _name go where Elasticsearch expects them for each query type:
 * inside the field object for field queries, next to the clauses otherwise.
 */
final class CommonOptionsTest extends TestCase
{
    /** @return iterable<string, array{QueryBuilder, string}> */
    public static function queries(): iterable
    {
        yield 'match' => [QueryBuilders::matchQuery('t', 'x'), '{"match":{"t":{"query":"x","boost":2,"_name":"q"}}}'];
        yield 'term' => [QueryBuilders::termQuery('t', 'x'), '{"term":{"t":{"value":"x","boost":2,"_name":"q"}}}'];
        yield 'range' => [QueryBuilders::rangeQuery('n')->gte(1), '{"range":{"n":{"gte":1,"boost":2,"_name":"q"}}}'];
        yield 'match_phrase' => [QueryBuilders::matchPhraseQuery('t', 'a b'), '{"match_phrase":{"t":{"query":"a b","boost":2,"_name":"q"}}}'];
        yield 'match_phrase_prefix' => [
            QueryBuilders::matchPhrasePrefixQuery('t', 'a b'),
            '{"match_phrase_prefix":{"t":{"query":"a b","boost":2,"_name":"q"}}}',
        ];
        yield 'terms' => [QueryBuilders::termsQuery('t', ['x', 'y']), '{"terms":{"t":["x","y"],"boost":2,"_name":"q"}}'];
        yield 'exists' => [QueryBuilders::existsQuery('t'), '{"exists":{"field":"t","boost":2,"_name":"q"}}'];
        yield 'match_all' => [QueryBuilders::matchAllQuery(), '{"match_all":{"boost":2,"_name":"q"}}'];
        yield 'bool' => [
            QueryBuilders::boolQuery()->must(QueryBuilders::termQuery('t', 'x')),
            '{"bool":{"must":[{"term":{"t":{"value":"x"}}}],"boost":2,"_name":"q"}}',
        ];
        yield 'geo_distance' => [
            QueryBuilders::geoDistanceQuery('pin')->distance('1km')->point(1.0, 2.0),
            '{"geo_distance":{"distance":"1km","pin":{"lat":1,"lon":2},"boost":2,"_name":"q"}}',
        ];
        yield 'geo_bounding_box' => [
            QueryBuilders::geoBoundingBoxQuery('pin')->topLeft('drm3btev3e86')->bottomRight('drm3btev3e8'),
            '{"geo_bounding_box":{"pin":{"top_left":"drm3btev3e86","bottom_right":"drm3btev3e8"},"boost":2,"_name":"q"}}',
        ];
    }

    #[DataProvider('queries')]
    public function testBoostAndNameArePlacedPerQueryType(QueryBuilder $query, string $expected): void
    {
        self::assertRenders($expected, $query->boost(2)->queryName('q')->toArray());
    }

    public function testQueriesWithoutOptionsAreUnchanged(): void
    {
        self::assertRenders('{"match_phrase":{"t":"a b"}}', QueryBuilders::matchPhraseQuery('t', 'a b')->toArray());
        self::assertRenders('{"match_all":{}}', QueryBuilders::matchAllQuery()->toArray());
    }
}
