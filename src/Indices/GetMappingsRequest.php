<?php

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class GetMappingsRequest extends Request
{
    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function getSource(): array
    {
        return [
            'index' => $this->indice
        ];
    }

}