<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class GeoCentroidBuilder extends AggregationBuilder
{
    private string $field;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

	public function getSource()
	{
		return [
			$this->name => [
				'geo_centroid' => [
					'field' => $this->field
				]
			]
		];
	}

	public function field(string $field): GeoCentroidBuilder
	{
		$this->field = $field;
		return $this;
	}

}