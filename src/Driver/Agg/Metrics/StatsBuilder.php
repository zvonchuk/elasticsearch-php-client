<?php

namespace Zvonchuk\Elastic\Driver\Agg\Metrics;

use Zvonchuk\Elastic\Driver\Aggregation;

class StatsBuilder extends Aggregation
{
	private $field = null;
	
	public function getSource()
	{
		return [
			$this->_name => [
				'stats' => [
					'field' => $this->field
				]
			]
		];
	}
	
	public function field($field): StatsBuilder
	{
		$this->field = $field;
		return $this;
	}
	
}