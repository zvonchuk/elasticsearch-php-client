<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Sort;

class FieldSort extends SortBuilder
{
    public function __construct(private readonly string $field)
    {
    }

    public function toArray(): array
    {
        return [$this->field => $this->order];
    }
}
