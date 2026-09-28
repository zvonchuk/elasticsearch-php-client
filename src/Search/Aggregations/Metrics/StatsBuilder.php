<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Metrics;

use Zvonchuk\Elastic\Search\Aggregations\MetricAggregationBuilder;

class StatsBuilder extends MetricAggregationBuilder
{
    private ?string $field = null;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function toArray(): array
    {
        return $this->render(['stats' => ['field' => $this->field]]);
    }
}
