<?php


namespace Zvonchuk\Elastic\Indices;


use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class Indices
{
    private \Elasticsearch\Client $elastic;

    public function __construct(Client $elastic)
    {
        $this->elastic = $elastic;
    }

    public function exists(IndexRequest $request)
    {
        return $this->elastic->indices()->exists($request->getSource());
    }

    public function create(CreateRequest $request)
    {
        return $this->elastic->indices()->create($request->getSource());
    }

    public function delete(DeleteRequest $request)
    {
        return $this->elastic->indices()->delete($request->getSource());
    }

    public function refresh(RefreshRequest $request)
    {
        return $this->elastic->indices()->refresh($request->getSource());
    }

    public function getMapping(GetMappingsRequest $request)
    {
        return $this->elastic->indices()->getMapping($request->getSource());
    }

    public function putMapping(PutMappingsRequest $request)
    {
        return $this->elastic->indices()->putMapping($request->getSource());
    }

}