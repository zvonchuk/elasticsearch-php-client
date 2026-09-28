<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Indices;

/**
 * A list of alias actions Elasticsearch applies atomically: readers see either the old or the new state.
 */
class UpdateAliasesRequest
{
    /** @var list<array<string, array<string, string>>> */
    private array $actions = [];

    public function add(string $index, string $alias): static
    {
        $this->actions[] = ['add' => ['index' => $index, 'alias' => $alias]];
        return $this;
    }

    public function remove(string $index, string $alias): static
    {
        $this->actions[] = ['remove' => ['index' => $index, 'alias' => $alias]];
        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->actions === [];
    }

    /** @return array{body: array{actions: list<array<string, array<string, string>>>}} */
    public function toArray(): array
    {
        return ['body' => ['actions' => $this->actions]];
    }
}
