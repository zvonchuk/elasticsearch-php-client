<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class GeoHashGridAggregationBuilder extends AggregationBuilder
{
    private int $_precision;
    private string $field;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getSource()
    {
        return $this->withSubAggregations([
            $this->name => [
                'geohash_grid' => [
                    'field' => $this->field,
                    'precision' => $this->_precision
                ]
            ]
        ]);
    }

    public function field(string $field): self
    {
        $this->field = $field;
        return $this;
    }

    public function precision(int $precision): self
    {
        $this->_precision = $precision;
        return $this;
    }
}