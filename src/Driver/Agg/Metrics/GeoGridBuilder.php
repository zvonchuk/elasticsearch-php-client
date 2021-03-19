<?php

namespace Zvonchuk\Elastic\Driver\Agg\Metrics;

use Zvonchuk\Elastic\Driver\Aggregation;

class GeoGridBuilder extends Aggregation
{
	private $field = null;
	private $_precision = null;
	private $_agg = false;
	
	public function getSource()
	{
		$return = [
			$this->_name => [
				'geohash_grid' => [
					'field' => $this->field,
					'precision' => $this->_precision
				]
			]
		];
		
		if ($this->_agg) {
			$return[$this->_name]['aggregations'] = $this->_agg;
		}
		
		return $return;
	}
	
	public function field($field): GeoGridBuilder
	{
		$this->field = $field;
		return $this;
	}
	
	public function precision(int $precision): GeoGridBuilder
	{
		$this->_precision = $precision;
		return $this;
	}
	
	public function subAggregation(Aggregation $agg): Aggregation
	{
		if ($this->_agg) {
			$this->_agg = array_merge($this->_agg, $agg->getSource());
		} else {
			$this->_agg = $agg->getSource();
		}
		
		return $this;
	}
	
}