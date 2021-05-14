<?php

namespace Zvonchuk\Elastic\Core;

class UpdateRequest extends Request
{
    public string $id;
    public array $source;

    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function id(string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function source(array $source)
    {
        $this->source = $source;
        return $this;
    }

    public function getSource(): array
    {
        return [
            'index' => $this->indice,
            'id' => $this->id,
            'body' => [
                'doc' => $this->source
            ]
        ];
    }

}