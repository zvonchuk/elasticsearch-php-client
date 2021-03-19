<?php

namespace Zvonchuk\Elastic\Driver\Query;

class RangeQueryBuilder extends QueryBuilder
{
    private string $field;
    private string $gte;
    private string $gt;
    private string $lte;
    private string $lt;

    public function __construct(string $field)
    {
        $this->name = 'range';
        $this->field = $field;
    }

    public function gte(string $gte): QueryBuilder
    {
        $this->gte = $gte;
        return $this;
    }

    public function gt(string $gt): QueryBuilder
    {
        $this->gt = $gt;
        return $this;
    }

    public function lte(string $lte): QueryBuilder
    {
        $this->lte = $lte;
        return $this;
    }

    public function lt(string $lt): QueryBuilder
    {
        $this->lt = $lt;
        return $this;
    }

    public function getSource()
    {
        $query = [];
        foreach (['gte', 'gt', 'lte', 'lt'] as $clause) {
            if (count($this->{$clause})) {
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