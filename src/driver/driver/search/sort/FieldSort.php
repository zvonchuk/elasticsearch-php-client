<?php

namespace Zvonchuk\Elastic\Driver\Search\Sort;

class FieldSort extends Sort
{
    public function getSource()
    {
        return [$this->field => $this->order];
    }
}