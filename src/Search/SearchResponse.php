<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

class SearchResponse
{
    /** @param array<string, mixed> $response raw Elasticsearch search response */
    public function __construct(private readonly array $response)
    {
    }

    /** @return array<string, mixed> */
    public function getAggregations(): array
    {
        return $this->response['aggregations'] ?? [];
    }

    /** @return list<array<string, mixed>> */
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
}
