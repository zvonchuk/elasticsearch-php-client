<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Sort;

abstract class SortBuilder implements SortInterface
{
    public const DESC = 'desc';
    public const ASC = 'asc';

    /** Ascending unless set otherwise, as in Elasticsearch (FieldSort on _score defaults to descending). */
    protected string $order = self::ASC;

    public function order(string $order): static
    {
        $this->order = $order;
        return $this;
    }

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
