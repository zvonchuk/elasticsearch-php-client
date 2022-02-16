<?php

namespace Zvonchuk\Elastic;


use Elasticsearch\ClientBuilder;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;
use Zvonchuk\Elastic\Core\ExistsRequest;
use Zvonchuk\Elastic\Core\GetRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;
use Zvonchuk\Elastic\Search\CountResponse;
use Zvonchuk\Elastic\Search\SearchResponse;

final class Client
{
    private static $instance;
    private static $hosts;
    private \Elasticsearch\Client $elastic;

    private function __construct(array $hosts)
    {
        $this->elastic = ClientBuilder::create()->setHosts($hosts)->build();
    }

    public static function getInstance(array $hosts): Client
    {
        if (is_null(static::$instance)) {
            static::$instance = new Client($hosts);
        }

        return static::$instance;
    }

    public function count(CountRequest $request)
    {
        $response = $this->elastic->count($request->getSource());
        return new CountResponse($response);
    }

    public function index(IndexRequest $request)
    {
        return $this->elastic->index($request->getSource());
    }

    public function search(SearchRequest $request): SearchResponse
    {
        $response = $this->elastic->search($request->getSource());
        return new SearchResponse($response);
    }

    public function update(UpdateRequest $request)
    {
        return $this->elastic->update($request->getSource());
    }

    public function delete(DeleteRequest $request)
    {
        return $this->elastic->delete($request->getSource());
    }

    public function get(GetRequest $request)
    {
        return $this->elastic->get($request->getSource());
    }

    public function exists(ExistsRequest $request)
    {
        return $this->elastic->exists($request->getSource());
    }

    public function bulk(BulkRequest $request)
    {
        return $this->elastic->bulk($request->getSource());
    }

    public function indices()
    {
        return new Indices\Indices($this->elastic);
    }
}