<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

class GetRequest extends DocumentRequest
{
    public function toArray(): array
    {
        return ['index' => $this->indice, 'id' => $this->requireId($this->id)];
    }
}
