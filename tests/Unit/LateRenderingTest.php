<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit;

use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

/**
 * A child added to a builder is rendered when the request is built, so
 * configuring it after adding it still counts.
 */
final class LateRenderingTest extends TestCase
{
    public function testBoolClauseConfiguredAfterBeingAdded(): void
    {
        $match = QueryBuilders::matchQuery('title', 'php elastic');
        $bool = QueryBuilders::boolQuery()->must($match);
        $match->operator('and');

        self::assertRenders('{"bool":{"must":[{"match":{"title":{"query":"php elastic","operator":"and"}}}]}}', $bool->getSource());
    }

    public function testSubAggregationConfiguredAfterBeingAdded(): void
    {
        $inner = AggregationBuilders::terms('by_tag');
        $outer = AggregationBuilders::histogram('h')->field('price')->interval(10)->subAggregation($inner);
        $inner->field('tag')->size(3);

        self::assertRenders(
            '{"h":{"histogram":{"field":"price","interval":10,"min_doc_count":0},"aggregations":{"by_tag":{"terms":{"field":"tag","size":3}}}}}',
            $outer->getSource(),
        );
    }

    public function testSortAndAggregationConfiguredAfterBeingAdded(): void
    {
        $sort = SortBuilders::fieldSort('price');
        $agg = AggregationBuilders::avg('avg_price');
        $source = (new SearchSourceBuilder())->sort($sort)->aggregation($agg);
        $sort->order(SortBuilder::ASC);
        $agg->field('price');

        self::assertRenders(
            '{"aggregations":{"avg_price":{"avg":{"field":"price"}}},"size":10,"from":0,"sort":{"price":"asc"}}',
            $source->getQuery(),
        );
    }
}
