<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Filter;

use Zvonchuk\Elastic\Query\QueryBuilder;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class FilterBuilder extends AggregationBuilder
{
    private QueryBuilder $filter;

    public function __construct(string $name, QueryBuilder $filter)
    {
        $this->name = $name;
        $this->filter = $filter;
    }

    public function getSource()
    {
        $return = [
            $this->name => [
                'filter' => $this->filter->getSource(),
            ]
        ];

        if ($this->aggregations) {
            $return[$this->name]['aggregations'] = $this->aggregations;
        }

        return $return;
    }
}
