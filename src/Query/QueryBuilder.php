<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

abstract class QueryBuilder implements QueryInterface
{
    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    /**
     * @deprecated since 1.0, use toArray(); will be removed in 2.0
     * @return array<string, mixed>
     */
    public function getSource(): array
    {
        return $this->toArray();
    }
}
