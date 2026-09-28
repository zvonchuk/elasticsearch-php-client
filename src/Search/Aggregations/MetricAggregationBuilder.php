<?php

namespace Zvonchuk\Elastic\Search\Aggregations;

/**
 * Metric aggregations compute a value; Elasticsearch does not accept sub-aggregations under them.
 */
abstract class MetricAggregationBuilder extends AggregationBuilder
{
    public function subAggregation(AggregationBuilder $subAggregation): AggregationBuilder
    {
        throw new \LogicException(sprintf(
            'Metric aggregation "%s" cannot have sub-aggregations; put them under a bucket aggregation instead.',
            $this->name,
        ));
    }
}
