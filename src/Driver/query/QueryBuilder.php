<?php

namespace Zvonchuk\Elastic\Driver\Query;

abstract class QueryBuilder
{
    public string $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    abstract public function getSource();
}