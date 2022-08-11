<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class AvgBuilder extends AggregationBuilder
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
                'avg' => [
                    'field' => $this->field
                ]
            ]
        ];
    }

    public function field(string $field): AvgBuilder
    {
        $this->field = $field;
        return $this;
    }

}