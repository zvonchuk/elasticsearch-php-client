<?php

namespace Zvonchuk\Elastic\Core;

use Zvonchuk\Elastic\Query\QueryBuilder;

class CountRequest extends Request
{
    private QueryBuilder $query;

    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function query(QueryBuilder $query)
    {
        $this->query = $query;
        return $this;
    }

    public function getSource(): array
    {
        return [
            'index' => $this->indice,
            'body' => [
                'query' => $this->query->getSource()
            ]
        ];
    }

}