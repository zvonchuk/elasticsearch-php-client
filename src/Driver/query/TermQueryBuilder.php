<?php

namespace Zvonchuk\Elastic\Driver\Query;

class TermQueryBuilder extends QueryBuilder
{
    private string $field;
    private string $value;

    public function __construct(string $field, string $value)
    {
        $this->name = 'term';
        $this->field = $field;
        $this->value = $value;
    }

    public function getSource()
    {
        return [
            $this->name => [
                $this->field => [
                    'value' => $this->value
                ]
            ]
        ];
    }
}