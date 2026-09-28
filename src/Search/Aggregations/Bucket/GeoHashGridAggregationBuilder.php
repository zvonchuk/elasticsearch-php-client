<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class GeoHashGridAggregationBuilder extends AggregationBuilder
{
    private ?string $field = null;
    private ?int $precision = null;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function precision(int $precision): static
    {
        $this->precision = $precision;
        return $this;
    }

    public function toArray(): array
    {
        return $this->render(['geohash_grid' => ['field' => $this->field, 'precision' => $this->precision]]);
    }
}
