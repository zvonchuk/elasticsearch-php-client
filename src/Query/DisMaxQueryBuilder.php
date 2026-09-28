<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

/**
 * Scores a document by its best matching query (plus tie_breaker × the others).
 */
class DisMaxQueryBuilder extends QueryBuilder
{
    /** @var list<QueryInterface> */
    private array $queries = [];
    private ?float $tieBreaker = null;

    public function add(QueryInterface $query): static
    {
        $this->queries[] = $query;
        return $this;
    }

    public function tieBreaker(float $tieBreaker): static
    {
        $this->tieBreaker = $tieBreaker;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['queries' => array_map(static fn (QueryInterface $query): array => $query->toArray(), $this->queries)];
        if ($this->tieBreaker !== null) {
            $body['tie_breaker'] = $this->tieBreaker;
        }

        return ['dis_max' => $body + $this->commonOptions()];
    }
}
