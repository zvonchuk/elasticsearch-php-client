<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class BoolQueryBuilder extends QueryBuilder
{
    /** @var array<string, list<QueryInterface>> Elasticsearch clause name => queries */
    private array $clauses = ['must' => [], 'must_not' => [], 'filter' => [], 'should' => []];

    public function must(QueryInterface $query): static
    {
        $this->clauses['must'][] = $query;
        return $this;
    }

    public function mustNot(QueryInterface $query): static
    {
        $this->clauses['must_not'][] = $query;
        return $this;
    }

    public function filter(QueryInterface $query): static
    {
        $this->clauses['filter'][] = $query;
        return $this;
    }

    public function should(QueryInterface $query): static
    {
        $this->clauses['should'][] = $query;
        return $this;
    }

    public function toArray(): array
    {
        $body = [];
        foreach ($this->clauses as $clause => $queries) {
            if ($queries !== []) {
                $body[$clause] = array_map(static fn (QueryInterface $query): array => $query->toArray(), $queries);
            }
        }

        return ['bool' => $body === [] ? new \stdClass() : $body];
    }
}
