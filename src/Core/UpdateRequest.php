<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

class UpdateRequest extends DocumentRequest
{
    /** @var array<string, mixed> */
    private array $source = [];

    /** @param array<string, mixed> $source partial document */
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
        return ['index' => $this->indice, 'id' => $this->requireId($this->id), 'body' => ['doc' => $this->source]];
    }
}
