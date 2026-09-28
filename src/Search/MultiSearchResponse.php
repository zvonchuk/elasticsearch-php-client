<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

class MultiSearchResponse implements \Countable
{
    /** @param array<string, mixed> $response raw Elasticsearch _msearch response */
    public function __construct(private readonly array $response)
    {
    }

    public function count(): int
    {
        return count($this->response['responses'] ?? []);
    }

    /**
     * The response of the search at $position (0-based, in the order added).
     *
     * @throws MultiSearchException when that search failed
     */
    public function get(int $position): SearchResponse
    {
        $item = $this->response['responses'][$position] ?? null;
        if ($item === null) {
            throw new \OutOfRangeException(sprintf('The multi search has no search #%d.', $position));
        }
        if (isset($item['error'])) {
            throw new MultiSearchException($position, $item['error']);
        }

        return new SearchResponse($item);
    }

    /**
     * Every response, in order.
     *
     * @throws MultiSearchException when any search failed
     * @return list<SearchResponse>
     */
    public function all(): array
    {
        $responses = [];
        for ($position = 0; $position < $this->count(); $position++) {
            $responses[] = $this->get($position);
        }

        return $responses;
    }

    /**
     * Errors by position, empty when every search succeeded.
     *
     * @return array<int, array<string, mixed>>
     */
    public function failures(): array
    {
        $failures = [];
        foreach ($this->response['responses'] ?? [] as $position => $item) {
            if (isset($item['error'])) {
                $failures[$position] = $item['error'];
            }
        }

        return $failures;
    }
}
