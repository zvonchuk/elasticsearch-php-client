<?php

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class PutMappingsRequest extends Request
{
    private ?array $properties = null;

    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function properties(array $properties) : self
    {
        $this->properties = $properties;
        return $this;
    }

    public function getSource(): array
    {
        $request['index'] = $this->indice;

        if(is_array($this->properties)) {
            $request['body']['properties'] = $this->properties;
        }

        return $request;
    }

}