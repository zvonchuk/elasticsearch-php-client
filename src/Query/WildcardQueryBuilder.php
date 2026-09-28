<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

/**
 * Pattern match on a keyword/term: "*" any characters, "?" one character. Leading wildcards are slow.
 */
class WildcardQueryBuilder extends QueryBuilder
{
    private ?bool $caseInsensitive = null;

    public function __construct(private readonly string $field, private readonly string $pattern)
    {
    }

    public function caseInsensitive(bool $caseInsensitive = true): static
    {
        $this->caseInsensitive = $caseInsensitive;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['value' => $this->pattern];
        if ($this->caseInsensitive !== null) {
            $body['case_insensitive'] = $this->caseInsensitive;
        }

        return ['wildcard' => [$this->field => $body + $this->commonOptions()]];
    }
}
