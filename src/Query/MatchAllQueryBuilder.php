<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MatchAllQueryBuilder extends QueryBuilder
{
    public function toArray(): array
    {
        return ['match_all' => new \stdClass()];
    }
}
