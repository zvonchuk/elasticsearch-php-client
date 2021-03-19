<?php

namespace Zvonchuk\Elastic\Driver\Agg\Filter;

use Zvonchuk\Elastic\Driver\Aggregation;

class FilterBuilder extends Aggregation
{
	private $_query = 0;
	private $_aggregations = false;
	
	public function __construct(string $name, array $term)
	{
		$this->_name = $name;
		$this->_query = $term;
	}
	
	public function getSource()
	{
		return [
			$this->_name => [
				'filter' => $this->_query,
				'aggregations' => $this->_aggregations,
			]
		];
	}
	
	public function subAggregation(Aggregation $agg): Aggregation
	{
		if ($this->_aggregations) {
			$this->_aggregations = array_merge($this->_aggregations, $agg->getSource());
		} else {
			$this->_aggregations = $agg->getSource();
		}
		
		return $this;
	}
}
