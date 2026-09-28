<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class TermsQueryBuilder extends QueryBuilder
{
    /** @param list<int|float|bool|string> $values */
    public function __construct(private readonly string $field, private readonly array $values)
    {
    }

    public function toArray(): array
    {
        return ['terms' => [$this->field => $this->values] + $this->commonOptions()];
    }
}
