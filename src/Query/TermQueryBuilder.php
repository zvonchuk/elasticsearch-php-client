<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class TermQueryBuilder extends QueryBuilder
{
    public function __construct(private readonly string $field, private readonly int|float|bool|string $value)
    {
    }

    public function toArray(): array
    {
        return ['term' => [$this->field => ['value' => $this->value] + $this->commonOptions()]];
    }
}
