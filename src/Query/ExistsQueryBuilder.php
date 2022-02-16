<?php

namespace Zvonchuk\Elastic\Query;

class ExistsQueryBuilder extends QueryBuilder
{
    private string $field;

    public function __construct(string $field)
    {
        $this->name = 'exists';
        $this->field = $field;
    }

    public function getSource()
    {
        return [
            $this->name => [
                "field" => $this->field
            ]
        ];
    }
}