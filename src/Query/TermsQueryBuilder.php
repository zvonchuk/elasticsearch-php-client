<?php

namespace Zvonchuk\Elastic\Query;

class TermsQueryBuilder extends QueryBuilder
{
    private string $field;
    private array $values;

    public function __construct(string $field, array $values)
    {
        $this->name = 'terms';
        $this->field = $field;
        $this->values = $values;
    }

    public function getSource()
    {
        return [
            $this->name => [
                $this->field => $this->values
            ]
        ];
    }
}