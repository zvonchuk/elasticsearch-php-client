<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\MetricAggregationBuilder;

/**
 * A metric aggregation; kept in the Bucket namespace for backward compatibility.
 */
class PercentilesBuilder extends MetricAggregationBuilder
{
    private ?string $field = null;
    /** @var list<int|float>|null null = Elasticsearch default (1, 5, 25, 50, 75, 95, 99) */
    private ?array $percents = null;
    private int|float $compression = 100;
    private bool $keyed = true;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    /** @param list<int|float> $percents */
    public function percents(array $percents): static
    {
        $this->percents = $percents;
        return $this;
    }

    public function compression(int|float $compression): static
    {
        $this->compression = $compression;
        return $this;
    }

    public function keyed(bool $keyed): static
    {
        $this->keyed = $keyed;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['field' => $this->field];
        if ($this->percents !== null) {
            $body['percents'] = $this->percents;
        }
        $body['tdigest'] = ['compression' => $this->compression];
        $body['keyed'] = $this->keyed;

        return $this->render(['percentiles' => $body]);
    }
}
