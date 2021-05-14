<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class HistogramBuilder extends AggregationBuilder
{
    private string $field;
    private int $_interval = 0;
    private int $_minDocCount = 0;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getSource()
    {
        return [
            $this->name => [
                'histogram' => [
                    'field' => $this->field,
                    'interval' => $this->_interval,
                    'min_doc_count' => $this->_minDocCount
                ]
            ]
        ];
    }

    public function minDocCount($minDocCount): self
    {
        $this->_minDocCount = $minDocCount;
        return $this;
    }

    public function field(string $field): self
    {
        $this->field = $field;
        return $this;
    }

    public function interval($interval): self
    {
        $this->_interval = $interval;
        return $this;
    }
}