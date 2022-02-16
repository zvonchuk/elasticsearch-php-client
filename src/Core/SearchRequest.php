<?php

namespace Zvonchuk\Elastic\Core;

use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

class SearchRequest extends Request
{
    private SearchSourceBuilder $source;

    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function source(SearchSourceBuilder $source)
    {
        $this->source = $source;
        return $this;
    }

    public function getSource(): array
    {
        return [
            'index' => $this->indice,
            'body' => $this->source->getQuery()
        ];
    }

}