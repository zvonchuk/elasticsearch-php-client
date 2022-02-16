<?php

namespace Zvonchuk\Elastic\Core;

class DeleteRequest extends Request
{
    public string $id;

    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function id(string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getSource(): array
    {
        return [
            'index' => $this->indice,
            'id' => $this->id
        ];
    }

}