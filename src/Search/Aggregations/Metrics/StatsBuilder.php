<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;

use Zvonchuk\Elastic\Search\Aggregations\MetricAggregationBuilder;

class StatsBuilder extends MetricAggregationBuilder
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
                'stats' => [
                    'field' => $this->field
                ]
            ]
        ];
    }

    public function field(string $field): StatsBuilder
    {
        $this->field = $field;
        return $this;
    }

}