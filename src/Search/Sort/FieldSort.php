<?php

namespace Zvonchuk\Elastic\Search\Sort;

class FieldSort extends SortBuilder
{
    public function getSource()
    {
        return [
            $this->field => $this->order
        ];
    }
}