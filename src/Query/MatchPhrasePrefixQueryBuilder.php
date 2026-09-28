<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MatchPhrasePrefixQueryBuilder extends QueryBuilder
{
    public function __construct(private readonly string $field, private readonly string $value)
    {
    }

    public function toArray(): array
    {
        $options = $this->commonOptions();
        $body = $options === [] ? $this->value : ['query' => $this->value] + $options;

        return ['match_phrase_prefix' => [$this->field => $body]];
    }
}
