<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

/**
 * Several searches in one round trip (_msearch). Responses come back in the order the searches were added.
 */
class MultiSearchRequest
{
    /** @var list<SearchRequest> */
    private array $searches = [];

    public function add(SearchRequest $search): static
    {
        $this->searches[] = $search;
        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->searches === [];
    }

    /** @return array{body: list<array<string, mixed>>} */
    public function toArray(): array
    {
        $body = [];
        foreach ($this->searches as $search) {
            $request = $search->toArray();
            $body[] = ['index' => $request['index']];
            $body[] = $request['body'] === [] ? new \stdClass() : $request['body'];
        }

        return ['body' => $body];
    }
}
