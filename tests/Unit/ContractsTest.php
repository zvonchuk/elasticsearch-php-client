<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit;

use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Query\QueryInterface;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationInterface;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortInterface;

final class ContractsTest extends TestCase
{
    public function testDeprecatedGetSourceIsTheSameAsToArray(): void
    {
        $query = QueryBuilders::boolQuery()->must(QueryBuilders::matchQuery('title', 'php'));
        $aggregation = AggregationBuilders::terms('by_tag')->field('tag');
        $sort = SortBuilders::fieldSort('price');
        $source = (new SearchSourceBuilder())->query($query)->aggregation($aggregation)->sort($sort);

        self::assertSame($query->toArray(), $query->getSource());
        self::assertSame($aggregation->toArray(), $aggregation->getSource());
        self::assertSame($sort->toArray(), $sort->getSource());
        self::assertSame($source->toArray(), $source->getQuery());
    }

    public function testOwnQueryAggregationAndSortImplementationsAreAccepted(): void
    {
        $query = new class () implements QueryInterface {
            public function toArray(): array
            {
                return ['match_none' => new \stdClass()];
            }
        };
        $aggregation = new class () implements AggregationInterface {
            public function getName(): string
            {
                return 'max_price';
            }

            public function toArray(): array
            {
                return ['max_price' => ['max' => ['field' => 'price']]];
            }
        };
        $sort = new class () implements SortInterface {
            public function toArray(): array
            {
                return ['_score' => 'desc'];
            }
        };

        $source = (new SearchSourceBuilder())
            ->query(QueryBuilders::boolQuery()->should($query))
            ->aggregation(AggregationBuilders::filter('f', $query)->subAggregation($aggregation))
            ->sort($sort);

        self::assertRenders(
            '{"query":{"bool":{"should":[{"match_none":{}}]}},"aggregations":{"f":{"filter":{"match_none":{}},'
            . '"aggregations":{"max_price":{"max":{"field":"price"}}}}},"size":10,"from":0,"sort":{"_score":"desc"}}',
            $source->toArray(),
        );
        self::assertRenders('{"index":"p","body":{"query":{"match_none":{}}}}', (new CountRequest('p'))->query($query)->toArray());
    }
}
