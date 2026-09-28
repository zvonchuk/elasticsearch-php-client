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
    /** @var list<int|float>|int */
    private array|int $percents = 0;
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
        return $this->render(['percentiles' => [
            'field' => $this->field,
            'percents' => $this->percents,
            'tdigest' => ['compression' => $this->compression],
            'keyed' => $this->keyed,
        ]]);
    }
}
