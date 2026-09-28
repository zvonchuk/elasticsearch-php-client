<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class QueryBuildersTest extends TestCase
{
    public function testMatch(): void
    {
        self::assertRenders(
            '{"match":{"title":{"query":"elastic search","operator":"and","fuzziness":"AUTO"}}}',
            QueryBuilders::matchQuery('title', 'elastic search')->operator('and')->fuzziness('AUTO')->getSource(),
        );
    }

    public function testMatchAll(): void
    {
        self::assertRenders('{"match_all":{}}', QueryBuilders::matchAllQuery()->getSource());
    }

    public function testMatchPhrase(): void
    {
        self::assertRenders('{"match_phrase":{"title":"quick fox"}}', QueryBuilders::matchPhraseQuery('title', 'quick fox')->getSource());
    }

    public function testMatchPhrasePrefix(): void
    {
        self::assertRenders('{"match_phrase_prefix":{"title":"quick f"}}', QueryBuilders::matchPhrasePrefixQuery('title', 'quick f')->getSource());
    }

    public function testTerm(): void
    {
        self::assertRenders('{"term":{"status":{"value":"active"}}}', QueryBuilders::termQuery('status', 'active')->getSource());
    }

    public function testTerms(): void
    {
        self::assertRenders('{"terms":{"tag":["a","b"]}}', QueryBuilders::termsQuery('tag', ['a', 'b'])->getSource());
    }

    public function testRangeWithStringBounds(): void
    {
        self::assertRenders(
            '{"range":{"created_at":{"gte":"2026-01-01","lt":"2027-01-01"}}}',
            QueryBuilders::rangeQuery('created_at')->gte('2026-01-01')->lt('2027-01-01')->getSource(),
        );
    }

    public function testExists(): void
    {
        self::assertRenders('{"exists":{"field":"email"}}', QueryBuilders::existsQuery('email')->getSource());
    }

    public function testBoolWithMustShouldFilter(): void
    {
        $query = QueryBuilders::boolQuery()
            ->must(QueryBuilders::matchQuery('title', 'php'))
            ->filter(QueryBuilders::termQuery('status', 'active'))
            ->should(QueryBuilders::termQuery('tag', 'news'));

        self::assertRenders(
            '{"bool":{"must":[{"match":{"title":{"query":"php"}}}],"filter":[{"term":{"status":{"value":"active"}}}],'
            . '"should":[{"term":{"tag":{"value":"news"}}}]}}',
            $query->getSource(),
        );
    }

    public function testEmptyBoolRendersAnEmptyObject(): void
    {
        self::assertRenders('{"bool":{}}', QueryBuilders::boolQuery()->getSource());
    }

    public function testGeoBoundingBox(): void
    {
        $query = QueryBuilders::geoBoundingBoxQuery('pin')
            ->topLeft(['lat' => 40.5, 'lon' => 49.7])
            ->bottomRight(['lat' => 40.3, 'lon' => 50.0]);

        self::assertRenders(
            '{"geo_bounding_box":{"pin":{"top_left":{"lat":40.5,"lon":49.7},"bottom_right":{"lat":40.3,"lon":50}}}}',
            $query->getSource(),
        );
    }
}
