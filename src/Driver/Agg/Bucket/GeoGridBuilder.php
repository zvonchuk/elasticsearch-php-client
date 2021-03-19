<?php

namespace Zvonchuk\Elastic\Driver\Agg\Bucket;

use Zvonchuk\Elastic\Driver\Aggregation;

class GeoGridBuilder extends Aggregation
{
	private $field = 0;
	private $_precision = 10;
	
	public function __construct(string $name)
	{
		$this->_name = $name;
	}
	
	public function getSource()
	{
		return [
			$this->_name => [
				'geohash_grid' => [
					'field' => $this->field,
					'precision' => $this->_precision
				]
			]
		];
	}
	
	public function precision(int $precision): GeoGridBuilder
	{
		$this->_precision = $precision;
		return $this;
	}
	
	public function field(string $field): GeoGridBuilder
	{
		$this->field = $field;
		return $this;
	}
	
	public function subAggregation(Aggregation $agg): Aggregation
	{
		if (isset($this->_agg)) {
			$this->_agg = array_merge($this->_agg, $agg->getSource());
		} else {
			$this->_agg = $agg->getSource();
		}
		
		return $this;
	}
}
