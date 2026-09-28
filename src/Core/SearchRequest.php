<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

class SearchRequest extends Request
{
    private SearchSourceBuilder $source;

    public function __construct(string $indice)
    {
        parent::__construct($indice);
        $this->source = new SearchSourceBuilder();
    }

    public function source(SearchSourceBuilder $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function toArray(): array
    {
        return ['index' => $this->indice, 'body' => $this->source->toArray()];
    }
}
