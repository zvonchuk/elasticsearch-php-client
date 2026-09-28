<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class DateHistogramBuilder extends AggregationBuilder
{
    public const SECOND = '1s';
    public const MINUTE = '1m';
    public const HOUR = '1h';
    public const DAY = '1d';
    public const WEEK = '1w';
    public const MONTH = '1M';
    public const QUARTER = '1q';
    public const YEAR = '1y';

    private ?string $field = null;
    private string $calendarInterval = self::DAY;
    private int $minDocCount = 0;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function calendarInterval(string $calendarInterval): static
    {
        $this->calendarInterval = $calendarInterval;
        return $this;
    }

    public function minDocCount(int $minDocCount): static
    {
        $this->minDocCount = $minDocCount;
        return $this;
    }

    public function toArray(): array
    {
        return $this->render(['date_histogram' => [
            'field' => $this->field,
            'calendar_interval' => $this->calendarInterval,
            'min_doc_count' => $this->minDocCount,
        ]]);
    }
}
