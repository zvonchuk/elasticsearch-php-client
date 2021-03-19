<?php

namespace Zvonchuk\Elastic\Driver\Query;

class MatchPhraseQueryBuilder extends QueryBuilder
{
    private string $field;
    private string $value;

    public function __construct(string $field, string $value)
    {
        $this->name = 'match_phrase';
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