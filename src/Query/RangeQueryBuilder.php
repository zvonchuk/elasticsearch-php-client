<?php

namespace Zvonchuk\Elastic\Query;

class RangeQueryBuilder extends QueryBuilder
{
    private string $field;
    private ?string $gte = null;
    private ?string $gt = null;
    private ?string $lte = null;
    private ?string $lt = null;

    public function __construct(string $field)
    {
        $this->name = 'range';
        $this->field = $field;
    }

    public function gte(string $gte): self
    {
        $this->gte = $gte;
        return $this;
    }

    public function gt(string $gt): self
    {
        $this->gt = $gt;
        return $this;
    }

    public function lte(string $lte): self
    {
        $this->lte = $lte;
        return $this;
    }

    public function lt(string $lt): self
    {
        $this->lt = $lt;
        return $this;
    }

    public function getSource()
    {
        $query = [];
        foreach (['gte', 'gt', 'lte', 'lt'] as $clause) {
            if (isset($this->{$clause})) {
                $query[$clause] = $this->{$clause};
            }
        }

        return [
            $this->name => [
                $this->field => $query
            ]
        ];
    }
}