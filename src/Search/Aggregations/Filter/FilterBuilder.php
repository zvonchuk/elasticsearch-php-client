<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Filter;

use Zvonchuk\Elastic\Query\QueryInterface;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class FilterBuilder extends AggregationBuilder
{
    public function __construct(string $name, private readonly QueryInterface $filter)
    {
        parent::__construct($name);
    }

    public function toArray(): array
    {
        return $this->render(['filter' => $this->filter->toArray()]);
    }
}
