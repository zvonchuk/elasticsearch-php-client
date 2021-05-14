<?php

namespace Zvonchuk\Elastic\Search\Aggregations;

use Zvonchuk\Elastic\Query\QueryBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\GeoHashGridAggregationBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\HistogramBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\PercentilesBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\TermsBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Filter\FilterBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Metrics\ExtendedStatsBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Metrics\GeoCentroidBuilder;
use Zvonchuk\Elastic\Search\Aggregations\Metrics\StatsBuilder;

class AggregationBuilders
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function histogram(string $name): HistogramBuilder
    {
        return new HistogramBuilder($name);
    }

    public static function percentiles(string $name): PercentilesBuilder
    {
        return new PercentilesBuilder($name);
    }

    public static function stats(string $name): StatsBuilder
    {
        return new StatsBuilder($name);
    }

    public static function extendedStats(string $name): ExtendedStatsBuilder
    {
        return new ExtendedStatsBuilder($name);
    }

    public static function terms(string $name): TermsBuilder
    {
        return new TermsBuilder($name);
    }

    public static function filter(string $name, QueryBuilder $filter): FilterBuilder
    {
        return new FilterBuilder($name, $filter);
    }

    public static function geoCentroid(string $name): GeoCentroidBuilder
    {
        return new GeoCentroidBuilder($name);
    }

    public static function geoHashGrid(string $name): GeoHashGridAggregationBuilder
    {
        return new GeoHashGridAggregationBuilder($name);
    }
}