<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class PutMappingsRequest extends Request
{
    /** @var array<string, mixed>|null */
    private ?array $properties = null;

    /** @param array<string, mixed> $properties */
    public function properties(array $properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    public function toArray(): array
    {
        $request = ['index' => $this->indice];
        if ($this->properties !== null) {
            $request['body']['properties'] = $this->properties;
        }

        return $request;
    }
}
