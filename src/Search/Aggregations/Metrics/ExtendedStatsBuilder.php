<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;


use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class ExtendedStatsBuilder extends AggregationBuilder
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
                'extended_stats' => [
                    'field' => $this->field
                ]
            ]
        ];
    }

    public function field(string $field): ExtendedStatsBuilder
    {
        $this->field = $field;
        return $this;
    }

}