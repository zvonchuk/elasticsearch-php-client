<?php

namespace Zvonchuk\Elastic\Query;

class MatchPhrasePrefixQueryBuilder extends QueryBuilder
{
    private string $field;
    private string $value;

    public function __construct(string $field, string $value)
    {
        $this->name = 'match_phrase_prefix';
        $this->field = $field;
        $this->value = $value;
    }

    public function getSource()
    {
        return [
            $this->name => [
                $this->field => $this->value
            ]
        ];
    }
}