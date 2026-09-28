<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class BoolQueryBuilder extends QueryBuilder
{
    /** @var array<string, list<QueryInterface>> Elasticsearch clause name => queries */
    private array $clauses = ['must' => [], 'must_not' => [], 'filter' => [], 'should' => []];
    private int|string|null $minimumShouldMatch = null;

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

    /**
     * How many should clauses must match: a number (2), a negative number (-1) or a percentage ("75%").
     */
    public function minimumShouldMatch(int|string $minimumShouldMatch): static
    {
        $this->minimumShouldMatch = $minimumShouldMatch;
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

        if ($this->minimumShouldMatch !== null) {
            $body['minimum_should_match'] = $this->minimumShouldMatch;
        }
        $body += $this->commonOptions();

        return ['bool' => $body === [] ? new \stdClass() : $body];
    }
}
