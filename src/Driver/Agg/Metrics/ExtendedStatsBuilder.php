<?php

namespace Zvonchuk\Elastic\Driver\Agg\Metrics;

use Zvonchuk\Elastic\Driver\Aggregation;

class ExtendedStatsBuilder extends Aggregation
{
	private $field = null;
	
	public function getSource()
	{
		return [
			$this->_name => [
				'extended_stats' => [
					'field' => $this->field
				]
			]
		];
	}
	
	public function field($field): ExtendedStatsBuilder
	{
		$this->field = $field;
		return $this;
	}
	
}