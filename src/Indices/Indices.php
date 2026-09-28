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
}
