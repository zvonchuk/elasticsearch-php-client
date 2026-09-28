<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Indices;

use Zvonchuk\Elastic\Core\Request;

class CreateRequest extends Request
{
    /** @var array<string, mixed>|null */
    private ?array $settings = null;
    /** @var array<string, mixed>|null */
    private ?array $mappings = null;
    /** @var array<string, array<string, mixed>> */
    private array $aliases = [];

    /** @param array<string, mixed> $settings */
    public function settings(array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    /** @param array<string, mixed> $mappings full mappings object, e.g. ['dynamic' => 'strict', 'properties' => [...]] */
    public function mappings(array $mappings): static
    {
        $this->mappings = $mappings;
        return $this;
    }

    /** @param array<string, mixed> $options alias options such as filter or is_write_index */
    public function alias(string $alias, array $options = []): static
    {
        $this->aliases[$alias] = $options;
        return $this;
    }

    public function toArray(): array
    {
        $request = ['index' => $this->indice];
        if ($this->settings !== null) {
            $request['body']['settings'] = $this->settings;
        }
        if ($this->mappings !== null) {
            $request['body']['mappings'] = $this->mappings;
        }
        if ($this->aliases !== []) {
            $request['body']['aliases'] = array_map(
                static fn (array $options): array|\stdClass => $options === [] ? new \stdClass() : $options,
                $this->aliases,
            );
        }

        return $request;
    }
}
