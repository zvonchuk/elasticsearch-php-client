<?php

namespace Zvonchuk\Elastic\Query;

abstract class QueryBuilder
{
    protected string $name;
    abstract public function getSource();
}