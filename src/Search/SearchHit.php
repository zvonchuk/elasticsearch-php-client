<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

final class SearchHit
{
    /**
     * @param array<string, mixed>        $source
     * @param list<string>                $matchedQueries names (_name) of the queries this hit matched
     * @param list<mixed>                 $sort           sort values, usable for search_after
     * @param array<string, list<string>> $highlight
     * @param array<string, mixed>|null   $explanation    score explanation, when the search ran with explain
     */
    public function __construct(
        public readonly string $index,
        public readonly string $id,
        public readonly ?float $score,
        public readonly array $source,
        public readonly array $matchedQueries = [],
        public readonly array $sort = [],
        public readonly array $highlight = [],
        public readonly ?array $explanation = null,
    ) {
    }

    /** @param array<string, mixed> $hit one element of hits.hits in a raw response */
    public static function fromArray(array $hit): self
    {
        return new self(
            index: (string) ($hit['_index'] ?? ''),
            id: (string) ($hit['_id'] ?? ''),
            score: isset($hit['_score']) ? (float) $hit['_score'] : null,
            source: $hit['_source'] ?? [],
            matchedQueries: array_values($hit['matched_queries'] ?? []),
            sort: array_values($hit['sort'] ?? []),
            highlight: $hit['highlight'] ?? [],
            explanation: $hit['_explanation'] ?? null,
        );
    }

    public function matched(string $queryName): bool
    {
        return in_array($queryName, $this->matchedQueries, true);
    }
}
