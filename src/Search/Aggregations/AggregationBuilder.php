<?php

namespace Zvonchuk\Elastic\Search\Aggregations;

abstract class AggregationBuilder
{
    protected string $name;
    protected ?array $aggregations = null;
    abstract public function getSource();

    public function subAggregation(AggregationBuilder $subAggregation): AggregationBuilder
    {
        if ($this->aggregations) {
            $this->aggregations = array_merge($this->aggregations, $subAggregation->getSource());
        } else {
            $this->aggregations = $subAggregation->getSource();
        }

        return $this;
    }

    /**
     * Adds the sub-aggregations, if any, next to the aggregation body.
     */
    protected function withSubAggregations(array $source): array
    {
        if ($this->aggregations) {
            $source[$this->name]['aggregations'] = $this->aggregations;
        }

        return $source;
    }
}
