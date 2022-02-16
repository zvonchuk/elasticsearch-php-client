<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Filter;

use Zvonchuk\Elastic\Query\QueryBuilder;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class FilterBuilder extends AggregationBuilder
{
    private QueryBuilder $filter;
    private ?array $aggregations = null;

    public function __construct(string $name, QueryBuilder $filter)
    {
        $this->name = $name;
        $this->filter = $filter;
    }

    public function getSource()
    {
        return [
            $this->name => [
                'filter' => $this->filter->getSource(),
                'aggregations' => $this->aggregations,
            ]
        ];
    }

    public function subAggregation(AggregationBuilder $subAggregation): AggregationBuilder
    {
        if ($this->aggregations) {
            $this->aggregations = array_merge($this->aggregations, $subAggregation->getSource());
        } else {
            $this->aggregations = $subAggregation->getSource();
        }

        return $this;
    }
}
