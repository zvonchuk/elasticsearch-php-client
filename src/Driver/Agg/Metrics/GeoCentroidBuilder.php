<?php

namespace Zvonchuk\Elastic\Driver\Agg\Metrics;

use Zvonchuk\Elastic\Driver\Aggregation;

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