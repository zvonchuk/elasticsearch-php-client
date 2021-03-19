<?php

namespace Zvonchuk\Elastic\Driver\Query;

class ExistsQueryBuilder extends QueryBuilder
{
    private string $field;
    private string $value;

    public function __construct(string $field, string $value)
    {
        $this->name = 'exists';
        $this->field = $field;
        $this->value = $value;
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