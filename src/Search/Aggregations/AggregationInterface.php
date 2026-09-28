<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations;

/**
 * Anything that renders to a named Elasticsearch aggregation: [name => body].
 */
interface AggregationInterface
{
    public function getName(): string;

    /** @return array<string, mixed> */
    public function toArray(): array;
}
