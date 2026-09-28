<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class RangeQueryBuilder extends QueryBuilder
{
    /** @var array<string, int|float|string> */
    private array $bounds = [];

    public function __construct(private readonly string $field)
    {
    }

    public function gte(int|float|string|\DateTimeInterface $gte): static
    {
        return $this->bound('gte', $gte);
    }

    public function gt(int|float|string|\DateTimeInterface $gt): static
    {
        return $this->bound('gt', $gt);
    }

    public function lte(int|float|string|\DateTimeInterface $lte): static
    {
        return $this->bound('lte', $lte);
    }

    public function lt(int|float|string|\DateTimeInterface $lt): static
    {
        return $this->bound('lt', $lt);
    }

    public function toArray(): array
    {
        $bounds = [];
        foreach (['gte', 'gt', 'lte', 'lt'] as $clause) {
            if (isset($this->bounds[$clause])) {
                $bounds[$clause] = $this->bounds[$clause];
            }
        }

        return ['range' => [$this->field => $bounds + $this->commonOptions()]];
    }

    private function bound(string $clause, int|float|string|\DateTimeInterface $value): static
    {
        $this->bounds[$clause] = $value instanceof \DateTimeInterface ? $value->format(\DateTimeInterface::ATOM) : $value;
        return $this;
    }
}
