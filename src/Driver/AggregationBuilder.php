<?php

namespace Zvonchuk\Elastic\Driver;

use Zvonchuk\Elastic\Driver\Agg\Bucket\HistogramBuilder;
use Zvonchuk\Elastic\Driver\Agg\Bucket\PercentilesBuilder;
use Zvonchuk\Elastic\Driver\Agg\Bucket\TermsBuilder;
use Zvonchuk\Elastic\Driver\Agg\Filter\FilterBuilder;
use Zvonchuk\Elastic\Driver\Agg\Metrics\ExtendedStatsBuilder;
use Zvonchuk\Elastic\Driver\Agg\Metrics\GeoCentroidBuilder;
use Zvonchuk\Elastic\Driver\Agg\Metrics\GeoGridBuilder;
use Zvonchuk\Elastic\Driver\Agg\Metrics\StatsBuilder;

class AggregationBuilder
{
    private string $_name;
	
	public function __construct(string $name)
	{
		$this->_name = $name;
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
	
	public static function filter(string $name, array $term): FilterBuilder
	{
		return new FilterBuilder($name, $term);
	}
	
	public static function geoCentroid(string $name): GeoCentroidBuilder
	{
		return new GeoCentroidBuilder($name);
	}
	
	public static function geoGrid(string $name): GeoGridBuilder
	{
		return new GeoGridBuilder($name);
	}
}