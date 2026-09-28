<?php


namespace Zvonchuk\Elastic\Search\Builder;


use Zvonchuk\Elastic\Query\QueryBuilder;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

class SearchSourceBuilder
{
    private ?QueryBuilder $query = null;
    /** @var AggregationBuilder[] */
    private array $aggregations = [];
    /** @var SortBuilder[] */
    private array $sort = [];
    private int $from = 0;
    private int $size = 10;
    private array $includeFields = [];
    private array $excludeFields = [];
    private array $searchAfter = [];

    public function include(array $includeFields)
    {
        $this->includeFields = $includeFields;
        return $this;
    }

    public function exclude(array $excludeFields)
    {
        $this->excludeFields = $excludeFields;
        return $this;
    }

    public function searchAfter(array $searchAfter)
    {
        $this->searchAfter = $searchAfter;
        return $this;
    }

    public function query(QueryBuilder $query)
    {
        $this->query = $query;
        return $this;
    }

    public function sort(SortBuilder $sort)
    {
        $this->sort[] = $sort;
        return $this;
    }

    public function aggregation(AggregationBuilder $aggregation)
    {
        $this->aggregations[] = $aggregation;
        return $this;
    }

    public function from(int $from)
    {
        $this->from = $from;
        return $this;
    }

    public function size(int $size)
    {
        $this->size = $size;
        return $this;
    }

    public function getQuery()
    {
        if ($this->query instanceof QueryBuilder) {
            $return['query'] = $this->query->getSource();
        }

        if (count($this->aggregations)) {
            $return['aggregations'] = array_merge(
                ...array_map(fn (AggregationBuilder $aggregation) => $aggregation->getSource(), $this->aggregations),
            );
        }

        $return['size'] = $this->size;
        $return['from'] = $this->from;

        if (count($this->includeFields) > 0) {
            $return['_source']['includes'] = $this->includeFields;
        }

        if (count($this->sort) > 0) {
            $return['sort'] = array_merge(...array_map(fn (SortBuilder $sort) => $sort->getSource(), $this->sort));
        }

        if (count($this->excludeFields) > 0) {
            $return['_source']['excludes'] = $this->excludeFields;
        }

        if (count($this->searchAfter) > 0) {
            $return['search_after'] = $this->searchAfter;
        }

        return $return;
    }
}