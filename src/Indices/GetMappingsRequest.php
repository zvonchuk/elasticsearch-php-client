<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class GetMappingsRequest extends Request
{
    public function toArray(): array
    {
        return ['index' => $this->indice];
    }
}
