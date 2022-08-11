<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class DateHistogramBuilder extends AggregationBuilder
{
    private string $field;
    private string $_calendarInterval = "1d";
    private int $_minDocCount = 0;

    public const SECOND = "1s";
    public const MINUTE = "1m";
    public const HOUR = "1h";
    public const DAY = "1d";
    public const WEEK = "1w";
    public const MONTH = "1M";
    public const QUARTER = "1q";
    public const YEAR = "1y";

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getSource()
    {
        $return = [
            $this->name => [
                'date_histogram' => [
                    'field' => $this->field,
                    'calendar_interval' => $this->_calendarInterval,
                    'min_doc_count' => $this->_minDocCount
                ],
            ],
        ];
        if (count($this->aggregations) > 0) {
            $return[$this->name]['aggregations'] = $this->aggregations;
        }

        return $return;
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

    public function calendarInterval(string $calendarInterval): self
    {
        $this->_calendarInterval = $calendarInterval;
        return $this;
    }
}