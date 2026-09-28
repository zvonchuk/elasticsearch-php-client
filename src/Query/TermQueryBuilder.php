<?php

namespace Zvonchuk\Elastic\Query;

class TermQueryBuilder extends QueryBuilder
{
    private string $field;
    private int|float|bool|string $value;

    public function __construct(string $field, int|float|bool|string $value)
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