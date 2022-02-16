<?php

namespace Zvonchuk\Elastic\Search\Sort;

abstract class SortBuilder
{
    public const DESC = "desc";
    public const ASC = "asc";
    protected ?string $field = null;
    protected ?string $order = self::DESC;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function order(string $order)
    {
        $this->order = $order;
        return $this;
    }

    abstract public function getSource();
}