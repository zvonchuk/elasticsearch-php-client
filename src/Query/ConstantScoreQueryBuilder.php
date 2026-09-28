<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

/**
 * Matches like its filter, but every hit gets the same score: the boost (1.0 by default).
 */
class ConstantScoreQueryBuilder extends QueryBuilder
{
    public function __construct(private readonly QueryInterface $filter)
    {
    }

    public function toArray(): array
    {
        return ['constant_score' => ['filter' => $this->filter->toArray()] + $this->commonOptions()];
    }
}
