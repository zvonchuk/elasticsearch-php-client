<?php

namespace Zvonchuk\Elastic\Driver\Agg\Bucket;

use Zvonchuk\Elastic\Driver\Aggregation;

class HistogramBuilder extends Aggregation
{
	private string $field;
    private int $_interval = 0;
    private int $_minDocCount = 0;
	
	public function getSource()
	{
		return [
			'histogram_' . $this->_name => [
				'histogram' => [
					'field' => $this->field,
					'interval' => $this->_interval,
					'min_doc_count' => $this->_minDocCount
				]
			]
		];
	}
	
	public function minDocCount($minDocCount): HistogramBuilder
	{
		$this->_minDocCount = $minDocCount;
		return $this;
	}
	
	public function field($field): HistogramBuilder
	{
		$this->field = $field;
		return $this;
	}
	
	public function interval($interval): HistogramBuilder
	{
		$this->_interval = $interval;
		return $this;
	}
}