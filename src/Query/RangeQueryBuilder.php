<?php

namespace Zvonchuk\Elastic\Query;

class RangeQueryBuilder extends QueryBuilder
{
    private string $field;
    /** @var array<string, int|float|string> */
    private array $bounds = [];

    public function __construct(string $field)
    {
        $this->name = 'range';
        $this->field = $field;
    }

    public function gte(int|float|string|\DateTimeInterface $gte): self
    {
        return $this->bound('gte', $gte);
    }

    public function gt(int|float|string|\DateTimeInterface $gt): self
    {
        return $this->bound('gt', $gt);
    }

    public function lte(int|float|string|\DateTimeInterface $lte): self
    {
        return $this->bound('lte', $lte);
    }

    public function lt(int|float|string|\DateTimeInterface $lt): self
    {
        return $this->bound('lt', $lt);
    }

    public function getSource()
    {
        $query = [];
        foreach (['gte', 'gt', 'lte', 'lt'] as $clause) {
            if (isset($this->bounds[$clause])) {
                $query[$clause] = $this->bounds[$clause];
            }
        }

        return [
            $this->name => [
                $this->field => $query
            ]
        ];
    }

    private function bound(string $clause, int|float|string|\DateTimeInterface $value): self
    {
        $this->bounds[$clause] = $value instanceof \DateTimeInterface ? $value->format(\DateTimeInterface::ATOM) : $value;
        return $this;
    }
}
