<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class AggregationBuildersTest extends TestCase
{
    public function testMetrics(): void
    {
        self::assertRenders('{"s":{"stats":{"field":"price"}}}', AggregationBuilders::stats('s')->field('price')->getSource());
        self::assertRenders('{"e":{"extended_stats":{"field":"price"}}}', AggregationBuilders::extendedStats('e')->field('price')->getSource());
        self::assertRenders('{"a":{"avg":{"field":"price"}}}', AggregationBuilders::avg('a')->field('price')->getSource());
        self::assertRenders('{"c":{"geo_centroid":{"field":"pin"}}}', AggregationBuilders::geoCentroid('c')->field('pin')->getSource());
    }

    public function testTerms(): void
    {
        self::assertRenders('{"t":{"terms":{"field":"tag","size":5}}}', AggregationBuilders::terms('t')->field('tag')->size(5)->getSource());
    }

    public function testHistogram(): void
    {
        self::assertRenders(
            '{"h":{"histogram":{"field":"price","interval":50,"min_doc_count":1}}}',
            AggregationBuilders::histogram('h')->field('price')->interval(50)->minDocCount(1)->getSource(),
        );
    }

    public function testPercentiles(): void
    {
        self::assertRenders(
            '{"p":{"percentiles":{"field":"took","percents":[50,95],"tdigest":{"compression":200},"keyed":false}}}',
            AggregationBuilders::percentiles('p')->field('took')->percents([50, 95])->compression(200)->keyed(false)->getSource(),
        );
    }

    public function testGeoHashGridWithSubAggregation(): void
    {
        $agg = AggregationBuilders::geoHashGrid('g')->field('pin')->precision(5)
            ->subAggregation(AggregationBuilders::geoCentroid('c')->field('pin'));

        self::assertRenders(
            '{"g":{"geohash_grid":{"field":"pin","precision":5},"aggregations":{"c":{"geo_centroid":{"field":"pin"}}}}}',
            $agg->getSource(),
        );
    }

    public function testDateHistogramWithSubAggregation(): void
    {
        $agg = AggregationBuilders::dateHistogram('d')->field('ts')->calendarInterval('1M')
            ->subAggregation(AggregationBuilders::avg('a')->field('price'));

        self::assertRenders(
            '{"d":{"date_histogram":{"field":"ts","calendar_interval":"1M","min_doc_count":0},"aggregations":{"a":{"avg":{"field":"price"}}}}}',
            $agg->getSource(),
        );
    }

    public function testDateHistogramWithoutSubAggregations(): void
    {
        self::assertRenders(
            '{"d":{"date_histogram":{"field":"ts","calendar_interval":"1d","min_doc_count":0}}}',
            AggregationBuilders::dateHistogram('d')->field('ts')->getSource(),
        );
    }

    public function testPercentilesWithoutPercentsLeavesTheElasticsearchDefault(): void
    {
        self::assertRenders(
            '{"p":{"percentiles":{"field":"took","tdigest":{"compression":100},"keyed":true}}}',
            AggregationBuilders::percentiles('p')->field('took')->toArray(),
        );
    }
}
