<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

/**
 * One search of a multi search failed; its results must not be treated as "no hits".
 */
final class MultiSearchException extends \RuntimeException
{
    /** @param array<string, mixed> $error the error object Elasticsearch returned for that search */
    public function __construct(public readonly int $position, public readonly array $error)
    {
        $reason = $error['root_cause'][0]['reason'] ?? $error['reason'] ?? json_encode($error);
        parent::__construct(sprintf('Search #%d of the multi search failed: %s', $position, $reason));
    }
}
