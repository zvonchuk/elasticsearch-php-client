<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class FilterAggregationTest extends TestCase
{
    public function testFilterWithoutSubAggregations(): void
    {
        self::assertRenders(
            '{"active":{"filter":{"term":{"status":{"value":"active"}}}}}',
            AggregationBuilders::filter('active', QueryBuilders::termQuery('status', 'active'))->getSource(),
        );
    }

    public function testFilterWithSubAggregations(): void
    {
        $agg = AggregationBuilders::filter('active', QueryBuilders::termQuery('status', 'active'))
            ->subAggregation(AggregationBuilders::avg('avg_price')->field('price'))
            ->subAggregation(AggregationBuilders::stats('price_stats')->field('price'));

        self::assertRenders(
            '{"active":{"filter":{"term":{"status":{"value":"active"}}},'
            . '"aggregations":{"avg_price":{"avg":{"field":"price"}},"price_stats":{"stats":{"field":"price"}}}}}',
            $agg->getSource(),
        );
    }
}
