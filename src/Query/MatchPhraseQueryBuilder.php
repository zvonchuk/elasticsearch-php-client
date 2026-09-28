<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MatchPhraseQueryBuilder extends QueryBuilder
{
    public function __construct(private readonly string $field, private readonly string $value)
    {
    }

    public function toArray(): array
    {
        return ['match_phrase' => [$this->field => $this->value]];
    }
}
