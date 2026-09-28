<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Sort;

/**
 * Anything that renders to one Elasticsearch sort entry.
 */
interface SortInterface
{
    /** @return array<string, mixed> */
    public function toArray(): array;
}
