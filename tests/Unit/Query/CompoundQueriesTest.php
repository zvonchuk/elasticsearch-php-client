<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

use InvalidArgumentException;
use Zvonchuk\Elastic\Query\NestedQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class CompoundQueriesTest extends TestCase
{
    public function testDisMax(): void
    {
        $query = QueryBuilders::disMaxQuery()
            ->add(QueryBuilders::matchQuery('name.exact', 'x'))
            ->add(QueryBuilders::matchQuery('name.skeleton', 'x'))
            ->tieBreaker(0.2)
            ->queryName('name');

        self::assertRenders(
            '{"dis_max":{"queries":[{"match":{"name.exact":{"query":"x"}}},{"match":{"name.skeleton":{"query":"x"}}}],'
            . '"tie_breaker":0.2,"_name":"name"}}',
            $query->toArray(),
        );
    }

    public function testConstantScore(): void
    {
        self::assertRenders(
            '{"constant_score":{"filter":{"term":{"b_year":{"value":"1950"}}},"boost":3,"_name":"dob_year"}}',
            QueryBuilders::constantScoreQuery(QueryBuilders::termQuery('b_year', '1950'))->boost(3)->queryName('dob_year')->toArray(),
        );
    }

    public function testNested(): void
    {
        $query = QueryBuilders::nestedQuery('documents', QueryBuilders::boolQuery()
            ->must(QueryBuilders::termQuery('documents.type', 'passport'))
            ->must(QueryBuilders::termQuery('documents.number', 'AZE1234567')))
            ->scoreMode(NestedQueryBuilder::SCORE_MAX)
            ->ignoreUnmapped()
            ->queryName('passport');

        self::assertRenders(
            '{"nested":{"path":"documents","query":{"bool":{"must":[{"term":{"documents.type":{"value":"passport"}}},'
            . '{"term":{"documents.number":{"value":"AZE1234567"}}}]}},"score_mode":"max","ignore_unmapped":true,"_name":"passport"}}',
            $query->toArray(),
        );
    }

    public function testNestedRejectsUnknownScoreMode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        QueryBuilders::nestedQuery('documents', QueryBuilders::matchAllQuery())->scoreMode('median');
    }
}
