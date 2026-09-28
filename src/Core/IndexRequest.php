<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

class IndexRequest extends DocumentRequest
{
    /** @var array<string, mixed> */
    private array $source = [];

    /** @param array<string, mixed> $source */
    public function source(array $source): static
    {
        $this->source = $source;
        return $this;
    }

    /** @return array<string, mixed> */
    public function getDocument(): array
    {
        return $this->source;
    }

    public function toArray(): array
    {
        $request = ['index' => $this->indice];
        if ($this->id !== null) {
            $request['id'] = $this->id;
        }
        $request['body'] = $this->source;

        return $request;
    }
}
