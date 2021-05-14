<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class PercentilesBuilder extends AggregationBuilder
{
    private string $field;
    private $_percents = 0;
    private $_compression = 100;
    private $_keyed = true;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getSource()
    {
        return [
            $this->name => [
                'percentiles' => [
                    'field' => $this->field,
                    'percents' => $this->_percents,
                    'tdigest' => [
                        'compression' => $this->_compression
                    ],
                    'keyed' => $this->_keyed,
                ]
            ]
        ];
    }

    public function percents(array $percents): PercentilesBuilder
    {
        $this->_percents = $percents;
        return $this;
    }

    public function compression($compression): PercentilesBuilder
    {
        $this->_compression = $compression;
        return $this;
    }

    public function keyed(bool $keyed): PercentilesBuilder
    {
        $this->_keyed = $keyed;
        return $this;
    }

    public function field(string $field): PercentilesBuilder
    {
        $this->field = $field;
        return $this;
    }
}
