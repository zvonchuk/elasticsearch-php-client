<?php

namespace Zvonchuk\Elastic\Driver\Search\Sort;

abstract class Sort
{
    protected ?string $field = null;
    protected ?string $order = 'desc';

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