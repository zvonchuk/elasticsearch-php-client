<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class CreateRequest extends Request
{
    /** @var array<string, mixed>|null */
    private ?array $settings = null;

    /** @param array<string, mixed> $settings */
    public function settings(array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    public function toArray(): array
    {
        $request = ['index' => $this->indice];
        if ($this->settings !== null) {
            $request['body']['settings'] = $this->settings;
        }

        return $request;
    }
}
