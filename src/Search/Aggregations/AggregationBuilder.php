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
}