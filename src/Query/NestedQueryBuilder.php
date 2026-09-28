<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

/**
 * Runs a query against nested objects of a "nested" field, each object matched on its own.
 */
class NestedQueryBuilder extends QueryBuilder
{
    public const SCORE_AVG = 'avg';
    public const SCORE_MAX = 'max';
    public const SCORE_MIN = 'min';
    public const SCORE_SUM = 'sum';
    public const SCORE_NONE = 'none';

    private const SCORE_MODES = [self::SCORE_AVG, self::SCORE_MAX, self::SCORE_MIN, self::SCORE_SUM, self::SCORE_NONE];

    private ?string $scoreMode = null;
    private ?bool $ignoreUnmapped = null;

    public function __construct(private readonly string $path, private readonly QueryInterface $query)
    {
    }

    public function scoreMode(string $scoreMode): static
    {
        if (!in_array($scoreMode, self::SCORE_MODES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown nested score mode "%s"; expected one of: %s.',
                $scoreMode,
                implode(', ', self::SCORE_MODES),
            ));
        }
        $this->scoreMode = $scoreMode;
        return $this;
    }

    /**
     * Match nothing instead of failing when the path is not mapped (e.g. searching several indices).
     */
    public function ignoreUnmapped(bool $ignoreUnmapped = true): static
    {
        $this->ignoreUnmapped = $ignoreUnmapped;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['path' => $this->path, 'query' => $this->query->toArray()];
        if ($this->scoreMode !== null) {
            $body['score_mode'] = $this->scoreMode;
        }
        if ($this->ignoreUnmapped !== null) {
            $body['ignore_unmapped'] = $this->ignoreUnmapped;
        }

        return ['nested' => $body + $this->commonOptions()];
    }
}
