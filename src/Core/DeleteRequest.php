<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

class DeleteRequest extends DocumentRequest
{
    public function toArray(): array
    {
        return ['index' => $this->indice, 'id' => $this->requireId($this->id)];
    }
}
