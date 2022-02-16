<?php

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class CreateRequest extends Request
{
    private ?array $settings = null;

    public function __construct(string $indice)
    {
        $this->indice = $indice;
    }

    public function settings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    public function getSource(): array
    {
        $request['index'] = $this->indice;

        if(is_array($this->settings)) {
            $request['body']['settings'] = $this->settings;
        }

        return $request;
    }
}