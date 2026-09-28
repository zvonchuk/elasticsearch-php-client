<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

abstract class QueryBuilder implements QueryInterface
{
    private ?float $boost = null;
    private ?string $queryName = null;

    /**
     * Relative weight of this query in the score.
     */
    public function boost(float $boost): static
    {
        $this->boost = $boost;
        return $this;
    }

    /**
     * Names the query (_name); matching hits report it in matched_queries.
     */
    public function queryName(string $queryName): static
    {
        $this->queryName = $queryName;
        return $this;
    }

    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    /**
     * @deprecated since 1.0, use toArray(); will be removed in 2.0
     * @return array<string, mixed>
     */
    public function getSource(): array
    {
        return $this->toArray();
    }

    /**
     * Parameters every Elasticsearch query accepts, to be merged into the query body.
     *
     * @return array{boost?: float, _name?: string}
     */
    protected function commonOptions(): array
    {
        $options = [];
        if ($this->boost !== null) {
            $options['boost'] = $this->boost;
        }
        if ($this->queryName !== null) {
            $options['_name'] = $this->queryName;
        }

        return $options;
    }
}
