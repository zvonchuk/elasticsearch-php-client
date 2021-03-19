<?php

namespace Yoxla\Elastic\Driver\Agg\Metrics;

use Yoxla\Elastic\Driver\Aggregation;

class GeoCentroidBuilder extends Aggregation
{
	private $field = null;
	
	public function getSource()
	{
		return [
			$this->_name => [
				'geo_centroid' => [
					'field' => $this->field
				]
			]
		];
	}
	
	public function field($field): GeoCentroidBuilder
	{
		$this->field = $field;
		return $this;
	}
	
}