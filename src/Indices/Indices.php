<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Indices;

use Elasticsearch\Client as ElasticsearchClient;

class Indices
{
    public function __construct(private readonly ElasticsearchClient $elastic)
    {
    }

    public function exists(IndexRequest $request): bool
    {
        return $this->elastic->indices()->exists($request->toArray());
    }

    /** @return array<string, mixed> */
    public function create(CreateRequest $request): array
    {
        return $this->elastic->indices()->create($request->toArray());
    }

    /** @return array<string, mixed> */
    public function delete(DeleteRequest $request): array
    {
        return $this->elastic->indices()->delete($request->toArray());
    }

    /** @return array<string, mixed> */
    public function refresh(RefreshRequest $request): array
    {
        return $this->elastic->indices()->refresh($request->toArray());
    }

    /** @return array<string, mixed> */
    public function getMapping(GetMappingsRequest $request): array
    {
        return $this->elastic->indices()->getMapping($request->toArray());
    }

    /** @return array<string, mixed> */
    public function putMapping(PutMappingsRequest $request): array
    {
        return $this->elastic->indices()->putMapping($request->toArray());
    }

    /** @return array<string, mixed> */
    public function updateAliases(UpdateAliasesRequest $request): array
    {
        return $this->elastic->indices()->updateAliases($request->toArray());
    }

    public function existsAlias(string $alias): bool
    {
        return $this->elastic->indices()->existsAlias(['name' => $alias]);
    }

    /**
     * Physical indices the alias points to; empty when the alias does not exist.
     *
     * @return list<string>
     */
    public function indicesForAlias(string $alias): array
    {
        if (!$this->existsAlias($alias)) {
            return [];
        }
        $indices = array_keys($this->elastic->indices()->getAlias(['name' => $alias]));
        sort($indices);

        return $indices;
    }

    /**
     * Points $alias at $newIndex only, in one atomic step, and returns the indices it was taken from
     * (so the caller can delete them once it is sure the new index is good).
     *
     * @return list<string>
     */
    public function swapAlias(string $alias, string $newIndex): array
    {
        $previous = array_values(array_filter($this->indicesForAlias($alias), static fn (string $index): bool => $index !== $newIndex));
        $request = (new UpdateAliasesRequest())->add($newIndex, $alias);
        foreach ($previous as $index) {
            $request->remove($index, $alias);
        }
        $this->updateAliases($request);

        return $previous;
    }
}
