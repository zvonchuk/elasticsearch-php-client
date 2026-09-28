<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class IdsQueryBuilder extends QueryBuilder
{
    /** @param list<string> $ids */
    public function __construct(private readonly array $ids)
    {
    }

    public function toArray(): array
    {
        return ['ids' => ['values' => $this->ids] + $this->commonOptions()];
    }
}
