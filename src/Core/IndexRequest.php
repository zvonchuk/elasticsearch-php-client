<?php

namespace Zvonchuk\Elastic\Core;

class IndexRequest extends Request
{
    public ?string $id = null;
    public array $source = [];

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
        $request = ['index' => $this->indice];
        if ($this->id !== null) {
            $request['id'] = $this->id;
        }
        $request['body'] = $this->source;

        return $request;
    }

}