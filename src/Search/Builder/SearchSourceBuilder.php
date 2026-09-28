<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Builder;

use Zvonchuk\Elastic\Query\QueryInterface;
use Zvonchuk\Elastic\Search\Aggregations\AggregationInterface;
use Zvonchuk\Elastic\Search\Sort\SortInterface;

class SearchSourceBuilder
{
    private ?QueryInterface $query = null;
    /** @var list<AggregationInterface> */
    private array $aggregations = [];
    /** @var list<SortInterface> */
    private array $sort = [];
    private int $from = 0;
    private int $size = 10;
    /** @var list<string> */
    private array $includeFields = [];
    /** @var list<string> */
    private array $excludeFields = [];
    /** @var list<mixed> */
    private array $searchAfter = [];
    private ?string $timeout = null;
    private bool|int|null $trackTotalHits = null;
    private ?float $minScore = null;
    private ?bool $explain = null;

    public function query(QueryInterface $query): static
    {
        $this->query = $query;
        return $this;
    }

    public function aggregation(AggregationInterface $aggregation): static
    {
        $this->aggregations[] = $aggregation;
        return $this;
    }

    public function sort(SortInterface $sort): static
    {
        $this->sort[] = $sort;
        return $this;
    }

    public function from(int $from): static
    {
        $this->from = $from;
        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    /** @param list<string> $includeFields */
    public function include(array $includeFields): static
    {
        $this->includeFields = $includeFields;
        return $this;
    }

    /** @param list<string> $excludeFields */
    public function exclude(array $excludeFields): static
    {
        $this->excludeFields = $excludeFields;
        return $this;
    }

    /** @param list<mixed> $searchAfter */
    public function searchAfter(array $searchAfter): static
    {
        $this->searchAfter = $searchAfter;
        return $this;
    }

    /**
     * Per-shard time limit, e.g. "2s"; on timeout Elasticsearch returns partial results with timed_out = true.
     */
    public function timeout(string $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * true: count all hits exactly; false: don't count (faster); int: count exactly up to this number.
     */
    public function trackTotalHits(bool|int $trackTotalHits): static
    {
        $this->trackTotalHits = $trackTotalHits;
        return $this;
    }

    /**
     * Leave out hits scoring below this value.
     */
    public function minScore(float $minScore): static
    {
        $this->minScore = $minScore;
        return $this;
    }

    /**
     * Ask Elasticsearch to explain how each hit's score was computed (SearchHit::$explanation). Debugging aid: slow.
     */
    public function explain(bool $explain = true): static
    {
        $this->explain = $explain;
        return $this;
    }

    /** @return array<string, mixed> the search request body */
    public function toArray(): array
    {
        $body = [];
        if ($this->query !== null) {
            $body['query'] = $this->query->toArray();
        }
        if ($this->aggregations !== []) {
            $body['aggregations'] = array_merge(
                ...array_map(static fn (AggregationInterface $aggregation): array => $aggregation->toArray(), $this->aggregations),
            );
        }
        $body['size'] = $this->size;
        $body['from'] = $this->from;
        if ($this->includeFields !== []) {
            $body['_source']['includes'] = $this->includeFields;
        }
        if ($this->sort !== []) {
            $body['sort'] = array_merge(...array_map(static fn (SortInterface $sort): array => $sort->toArray(), $this->sort));
        }
        if ($this->excludeFields !== []) {
            $body['_source']['excludes'] = $this->excludeFields;
        }
        if ($this->searchAfter !== []) {
            $body['search_after'] = $this->searchAfter;
        }
        if ($this->timeout !== null) {
            $body['timeout'] = $this->timeout;
        }
        if ($this->trackTotalHits !== null) {
            $body['track_total_hits'] = $this->trackTotalHits;
        }
        if ($this->minScore !== null) {
            $body['min_score'] = $this->minScore;
        }
        if ($this->explain !== null) {
            $body['explain'] = $this->explain;
        }

        return $body;
    }

    /**
     * @deprecated since 1.0, use toArray(); will be removed in 2.0
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $this->toArray();
    }
}
