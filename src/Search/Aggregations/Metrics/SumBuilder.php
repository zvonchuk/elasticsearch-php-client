<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;

use Zvonchuk\Elastic\Search\Aggregations\MetricAggregationBuilder;

class SumBuilder extends MetricAggregationBuilder
{
    private ?string $field = null;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function toArray(): array
    {
        return $this->render(['sum' => ['field' => $this->field]]);
    }
}
