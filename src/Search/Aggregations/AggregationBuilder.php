<?php

namespace Zvonchuk\Elastic\Search\Aggregations;

abstract class AggregationBuilder
{
    protected string $name;
    abstract public function getSource();
}