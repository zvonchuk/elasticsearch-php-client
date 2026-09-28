<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MatchAllQueryBuilder extends QueryBuilder
{
    public function toArray(): array
    {
        $options = $this->commonOptions();

        return ['match_all' => $options === [] ? new \stdClass() : $options];
    }
}
