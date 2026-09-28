<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class PrefixQueryBuilder extends QueryBuilder
{
    private ?bool $caseInsensitive = null;

    public function __construct(private readonly string $field, private readonly string $value)
    {
    }

    public function caseInsensitive(bool $caseInsensitive = true): static
    {
        $this->caseInsensitive = $caseInsensitive;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['value' => $this->value];
        if ($this->caseInsensitive !== null) {
            $body['case_insensitive'] = $this->caseInsensitive;
        }

        return ['prefix' => [$this->field => $body + $this->commonOptions()]];
    }
}
