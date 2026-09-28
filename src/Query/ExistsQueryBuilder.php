<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class ExistsQueryBuilder extends QueryBuilder
{
    public function __construct(private readonly string $field)
    {
    }

    public function toArray(): array
    {
        return ['exists' => ['field' => $this->field] + $this->commonOptions()];
    }
}
