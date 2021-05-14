<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class GeoHashGridAggregationBuilder extends AggregationBuilder
{
    private int $_precision;
    private string $field;
    private ?array $_agg = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getSource()
    {
        $return = [
            $this->name => [
                'geohash_grid' => [
                    'field' => $this->field,
                    'precision' => $this->_precision
                ]
            ]
        ];

        if ($this->_agg) {
            $return[$this->name]['aggregations'] = $this->_agg;
        }

        return $return;
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

    public function subAggregation(AggregationBuilder $agg): AggregationBuilder
    {
        if (is_array($this->_agg)) {
            $this->_agg = array_merge($this->_agg, $agg->getSource());
        } else {
            $this->_agg = $agg->getSource();
        }

        return $this;
    }

}