<?php

namespace Zvonchuk\Elastic\Search\Aggregations;

abstract class AggregationBuilder
{
    protected string $name;
    /** @var AggregationBuilder[] */
    protected array $aggregations = [];
    abstract public function getSource();

    public function subAggregation(AggregationBuilder $subAggregation): AggregationBuilder
    {
        $this->aggregations[] = $subAggregation;

        return $this;
    }

    /**
     * Adds the sub-aggregations, if any, next to the aggregation body.
     */
    protected function withSubAggregations(array $source): array
    {
        if ($this->aggregations) {
            $source[$this->name]['aggregations'] = array_merge(
                ...array_map(fn (AggregationBuilder $aggregation) => $aggregation->getSource(), $this->aggregations),
            );
        }

        return $source;
    }
}
