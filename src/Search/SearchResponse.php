<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

class SearchResponse
{
    /** @param array<string, mixed> $response raw Elasticsearch search response */
    public function __construct(private readonly array $response)
    {
    }

    /** @return list<SearchHit> */
    public function hits(): array
    {
        return array_map(static fn (array $hit): SearchHit => SearchHit::fromArray($hit), $this->getHits());
    }

    /**
     * Raw hits as returned by Elasticsearch; prefer hits().
     *
     * @return list<array<string, mixed>>
     */
    public function getHits(): array
    {
        return $this->response['hits']['hits'] ?? [];
    }

    /**
     * Number of matching documents, or null when the search ran with track_total_hits disabled.
     */
    public function getTotal(): ?int
    {
        return $this->response['hits']['total']['value'] ?? null;
    }

    /**
     * "eq" when getTotal() is exact, "gte" when it is a lower bound; null when not tracked.
     */
    public function totalRelation(): ?string
    {
        return $this->response['hits']['total']['relation'] ?? null;
    }

    public function maxScore(): ?float
    {
        $maxScore = $this->response['hits']['max_score'] ?? null;
        return $maxScore === null ? null : (float) $maxScore;
    }

    /** Milliseconds Elasticsearch spent on the search. */
    public function took(): ?int
    {
        return isset($this->response['took']) ? (int) $this->response['took'] : null;
    }

    /**
     * True when the search hit its timeout: the hits are partial.
     */
    public function timedOut(): bool
    {
        return (bool) ($this->response['timed_out'] ?? false);
    }

    /**
     * Shards that failed; their documents are missing from the result.
     */
    public function failedShards(): int
    {
        return (int) ($this->response['_shards']['failed'] ?? 0);
    }

    /** @return array<string, mixed> */
    public function getAggregations(): array
    {
        return $this->response['aggregations'] ?? [];
    }

    /** @return array<string, mixed> the raw response */
    public function toArray(): array
    {
        return $this->response;
    }
}
