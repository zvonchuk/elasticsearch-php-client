<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

/**
 * Anything that renders to an Elasticsearch query clause.
 */
interface QueryInterface
{
    /** @return array<string, mixed> */
    public function toArray(): array;
}
