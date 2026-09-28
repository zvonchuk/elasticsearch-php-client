<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

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
}
