<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic;

use Elasticsearch\ClientBuilder;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;
use Zvonchuk\Elastic\Core\ExistsRequest;
use Zvonchuk\Elastic\Core\GetRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\MultiSearchRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;
use Zvonchuk\Elastic\Indices\Indices;
use Zvonchuk\Elastic\Search\CountResponse;
use Zvonchuk\Elastic\Search\MultiSearchResponse;
use Zvonchuk\Elastic\Search\SearchResponse;

final class Client
{
    private static ?Client $instance = null;
    private \Elasticsearch\Client $elastic;

    /** @param list<string|array<string, mixed>> $hosts */
    private function __construct(array $hosts)
    {
        $this->elastic = ClientBuilder::create()->setHosts($hosts)->build();
    }

    /** @param list<string|array<string, mixed>> $hosts */
    public static function getInstance(array $hosts): Client
    {
        if (self::$instance === null) {
            self::$instance = new Client($hosts);
        }

        return self::$instance;
    }

    public function count(CountRequest $request): CountResponse
    {
        return new CountResponse($this->elastic->count($request->toArray()));
    }

    /** @return array<string, mixed> */
    public function index(IndexRequest $request): array
    {
        return $this->elastic->index($request->toArray());
    }

    public function search(SearchRequest $request): SearchResponse
    {
        return new SearchResponse($this->elastic->search($request->toArray()));
    }

    public function msearch(MultiSearchRequest $request): MultiSearchResponse
    {
        if ($request->isEmpty()) {
            return new MultiSearchResponse(['responses' => []]);
        }

        return new MultiSearchResponse($this->elastic->msearch($request->toArray()));
    }

    /** @return array<string, mixed> */
    public function update(UpdateRequest $request): array
    {
        return $this->elastic->update($request->toArray());
    }

    /** @return array<string, mixed> */
    public function delete(DeleteRequest $request): array
    {
        return $this->elastic->delete($request->toArray());
    }

    /** @return array<string, mixed> */
    public function get(GetRequest $request): array
    {
        return $this->elastic->get($request->toArray());
    }

    public function exists(ExistsRequest $request): bool
    {
        return $this->elastic->exists($request->toArray());
    }

    /** @return array<string, mixed> */
    public function bulk(BulkRequest $request): array
    {
        return $this->elastic->bulk($request->toArray());
    }

    public function indices(): Indices
    {
        return new Indices($this->elastic);
    }
}
