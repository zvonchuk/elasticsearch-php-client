<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class StatsBuilder extends AggregationBuilder
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