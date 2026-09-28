<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class SubAggregationsTest extends TestCase
{
    public function testTermsKeepsSubAggregations(): void
    {
        $agg = AggregationBuilders::terms('by_tag')->field('tag')
            ->subAggregation(AggregationBuilders::avg('avg_price')->field('price'));

        self::assertRenders(
            '{"by_tag":{"terms":{"field":"tag","size":10},"aggregations":{"avg_price":{"avg":{"field":"price"}}}}}',
            $agg->getSource(),
        );
    }

    public function testHistogramKeepsSubAggregations(): void
    {
        $agg = AggregationBuilders::histogram('h')->field('price')->interval(50)
            ->subAggregation(AggregationBuilders::terms('by_tag')->field('tag'));

        self::assertRenders(
            '{"h":{"histogram":{"field":"price","interval":50,"min_doc_count":0},"aggregations":{"by_tag":{"terms":{"field":"tag","size":10}}}}}',
            $agg->getSource(),
        );
    }

    public function testNestedBucketsRenderAllLevels(): void
    {
        $agg = AggregationBuilders::terms('by_tag')->field('tag')
            ->subAggregation(
                AggregationBuilders::dateHistogram('per_month')->field('ts')->calendarInterval('1M')
                    ->subAggregation(AggregationBuilders::stats('price')->field('price')),
            );

        self::assertRenders(
            '{"by_tag":{"terms":{"field":"tag","size":10},"aggregations":{"per_month":{"date_histogram":'
            . '{"field":"ts","calendar_interval":"1M","min_doc_count":0},"aggregations":{"price":{"stats":{"field":"price"}}}}}}}',
            $agg->getSource(),
        );
    }

    /** @return iterable<string, array{AggregationBuilder}> */
    public static function metrics(): iterable
    {
        yield 'avg' => [AggregationBuilders::avg('m')->field('x')];
        yield 'stats' => [AggregationBuilders::stats('m')->field('x')];
        yield 'extended_stats' => [AggregationBuilders::extendedStats('m')->field('x')];
        yield 'geo_centroid' => [AggregationBuilders::geoCentroid('m')->field('x')];
        yield 'percentiles' => [AggregationBuilders::percentiles('m')->field('x')];
        yield 'sum' => [AggregationBuilders::sum('m')->field('x')];
    }

    #[DataProvider('metrics')]
    public function testMetricsRejectSubAggregations(AggregationBuilder $metric): void
    {
        $this->expectException(LogicException::class);
        $metric->subAggregation(AggregationBuilders::avg('inner')->field('y'));
    }
}
