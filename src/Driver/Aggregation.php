<?php

namespace Zvonchuk\Elastic\Driver;

abstract class Aggregation
{
    public ?string $_name = null;

    public function __construct(string $name)
    {
        $this->_name = $name;
    }

    abstract public function getSource();

}