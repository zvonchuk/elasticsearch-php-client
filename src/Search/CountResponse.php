<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

class CountResponse
{
    /** @param array<string, mixed> $response raw Elasticsearch count response */
    public function __construct(private readonly array $response)
    {
    }

    public function getCount(): int
    {
        return (int) $this->response['count'];
    }
}
