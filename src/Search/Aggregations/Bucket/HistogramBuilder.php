<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class HistogramBuilder extends AggregationBuilder
{
    private ?string $field = null;
    private int|float $interval = 0;
    private int $minDocCount = 0;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function interval(int|float $interval): static
    {
        $this->interval = $interval;
        return $this;
    }

    public function minDocCount(int $minDocCount): static
    {
        $this->minDocCount = $minDocCount;
        return $this;
    }

    public function toArray(): array
    {
        return $this->render(['histogram' => [
            'field' => $this->field,
            'interval' => $this->interval,
            'min_doc_count' => $this->minDocCount,
        ]]);
    }
}
