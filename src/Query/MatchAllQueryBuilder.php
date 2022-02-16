<?php

namespace Zvonchuk\Elastic\Query;

class MatchAllQueryBuilder extends QueryBuilder
{
    public function __construct()
    {
        $this->name = 'match_all';
    }

    public function getSource()
    {
        return [
            $this->name => new \stdClass()
        ];
    }
}