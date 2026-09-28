<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

use Zvonchuk\Elastic\Query\QueryInterface;

class CountRequest extends Request
{
    private ?QueryInterface $query = null;

    public function query(QueryInterface $query): static
    {
        $this->query = $query;
        return $this;
    }

    public function toArray(): array
    {
        $request = ['index' => $this->indice];
        if ($this->query !== null) {
            $request['body'] = ['query' => $this->query->toArray()];
        }

        return $request;
    }
}
