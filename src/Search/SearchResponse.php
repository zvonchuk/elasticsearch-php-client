<?php


namespace Zvonchuk\Elastic\Search;


class SearchResponse
{
    private array $response = [];

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getAggregations(): array
    {
        return $this->response['aggregations'] ?? [];
    }

    public function getHits(): array
    {
        return $this->response['hits']['hits'];
    }

    /**
     * Number of matching documents, or null when the search ran with track_total_hits disabled.
     */
    public function getTotal(): ?int
    {
        return $this->response['hits']['total']['value'] ?? null;
    }

}