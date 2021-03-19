<?php

namespace Zvonchuk\Elastic\Driver;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Exception;
use Zvonchuk\Elastic\Driver\Query\BoolQueryBuilder;
use Zvonchuk\Elastic\Driver\Query\QueryBuilder;
use Zvonchuk\Elastic\Driver\Query\QueryBuilders;
use Zvonchuk\Elastic\Driver\Search\Sort\Sort;

class Builder
{
    public const PAGE_LIMIT = 20;
    private array $_query = [];
	private $_source = null;
	private $_caller = null;
    private string $_searchSuffix = '_search';
	private $_result = null;
	private $_prevQuery = null;
	private $_isDebug = false;
    private int $_page = 1;
	private $_scrollId = null;
	private $_minShouldMatch = null;
	private $_saveQuery = false;
	private $_isScroll = false;
	private $addTotalCount = false;
    private int $_count = 0;
    private array $_sort = [];
    private array $_searchAfter = [];

    public function __construct($_source, $_caller)
	{
		$this->_source = $_source;
		$this->_caller = $_caller;
	}
	
	public function mustNot(array $request)
	{
		$this->_query['query']['bool']['must_not'][] = $request;
		return $this;
	}
	
	public function filter(array $request)
	{
		$this->_query['query']['bool']['filter'][] = $request;
		return $this;
	}
	
	public function should(array $request)
	{
		$this->_query['query']['bool']['should'][] = $request;
		return $this;
	}
	
	public function minShouldMatch(int $min)
	{
		$this->_query['query']['bool']['minimum_should_match'] = $min;
		$this->_minShouldMatch = $min;
		return $this;
	}
	
	public function minScore(int $score)
	{
		$this->_query['min_score'] = $score;
		return $this;
	}
	
	public function aggregation(Aggregation $agg): Builder
	{
		if (isset($this->_query['aggregations'])) {
			$this->_query['aggregations'] = array_merge($this->_query['aggregations'], $agg->getSource());
		} else {
			$this->_query['aggregations'] = $agg->getSource();
		}
		
		return $this;
	}

    public function query(QueryBuilder $query) {

        $this->_query['query'] = $query->getSource();
        return $this;;
    }
	
	public function scroll(?string $scroll = null)
	{
		if (!empty ($scroll)) $this->_scrollId = $scroll;
		return $this;
	}
	
	// Pagination & Sorting
	public function paging(?int $id = null, ?string $parameter = null)
	{
		if (empty($parameter)) $parameter = 'lt';
		
		$this->must((new Range())->{$parameter}('id', $id));
		return $this;
	}

	
	public function must(array $request)
	{
		$this->_query['query']['bool']['must'][] = $request;
		return $this;
	}
	
	public function page(int $page = 1, int $limit = null)
	{
		$this->_page = $page;
		[$this->_query['from'], $this->_query['size']] = Limit::getValue($page, $limit);
		return $this;
	}
	
	public function withCount()
	{
		$this->addTotalCount = true;
		
		return $this;
	}
	
	public function fromSize(int $from = 0, int $limit = Limit::PAGE_LIMIT)
	{
		[$this->_query['from'], $this->_query['size']] = [$from, $limit];
		return $this;
	}
	
	
	public function offset(int $from = 0)
	{
		$this->_query['from'] = $from;
		return $this;
	}
	
	public function limit(int $size = Limit::PAGE_LIMIT)
	{
		$this->_query['size'] = $size;
		return $this;
	}

    public function sort(Sort $sort)
    {
        $this->_sort[] = $sort->getSource();
        return $this;
    }
	
	public function getCounter()
	{
		$this->_searchSuffix = '_count';
		unset ($this->_query['Sort'], $this->_query['from'], $this->_query['size']);
		return $this;
	}
	
	public function saveQueryAlert()
	{
		$this->_saveQuery = true;
	}
	
	/**
	 * @return $this
	 * @throws Exception
	 * @throws BadRequest400Exception
	 */
	public function execute()
	{
        if(count($this->_sort) > 0) $this->_query['Sort'] = $this->_sort;
		if(count($this->_query) < 1) throw new Exception('Empty Elastic query');

		if (true === $this->_isDebug) {
			echo json_encode($this->_query) . '<pre>';
			echo print_r($this->_query, 1) . '<hr>';
		}

		$data = $this->_caller->search($this->_query);

		if ($this->addTotalCount) {
			unset($this->_query['Sort']);
			unset($this->_query['from']);
			unset($this->_query['size']);
			unset($this->_query['search_after']);
			$this->_count = $this->_caller->count($this->_query)['count'];
		}
		
		//if (!empty ($data['_scroll_id'])) $this->_scrollId = $data['_scroll_id'];
		
		$this->_result = $data;
		$this->_prevQuery = $this->_query;
		$this->_query = [];
		$this->_isDebug = false;
		
		return $this;
	}
	
	public function fetch()
	{
        $next = "";
		if (empty ($this->_result)) throw new Exception('Result is empty');
		
		if (isset ($this->_result['count'])) {
			return $this->_result['count'];
		}
		
		if (isset ($this->_result['hits']['hits'])) {

		    $list = array_map(function ($v) {
				return $v['_source'];
			}, $this->_result['hits']['hits']);

            if (!empty(end($list)['id'])) {
                $next = ($this->_result['hits']['total']['value'] > Limit::PAGE_LIMIT ? base64_encode(json_encode(
                    end($this->_result['hits']['hits'])['Sort'])
                ) : "");
            }

			$return = [
				'list' => $list,
				'total' => $this->_count == 0 ? $this->_result['hits']['total']['value'] : $this->_count,
				'paginator' => [
					//'next_id' => 0,
					'next' => $next,
					'total' => $this->_count == 0 ? $this->_result['hits']['total']['value'] : $this->_count,
					//'pages' => ceil($this->_result['hits']['total']['value'] / Limit::PAGE_LIMIT),
				]
			];

//			if (!empty(end($list)['id'])) {
//				$return['paginator']['next_id'] = ($this->_result['hits']['total']['value'] > Limit::PAGE_LIMIT ? end($list)['id'] : 0);
//			}

			//$return['paginator']['next_page'] = $this->_page < $return['paginator']['pages'] ? $this->_page + 1 : 0;
			//$return['paginator']['scroll'] = $this->_scrollId;


			return $return;
		}
	}
	
	public function fetchAgg()
	{
		
		if (empty ($this->_result)) throw new Exception('Result is empty');
		
		$return = [
			'aggregations' => [],
			'total' => 0,
		];
		
		if (isset ($this->_result['aggregations'])) {
			$return = [
				'aggregations' => $this->_result['aggregations'],
				'total' => $this->_result['hits']['total'],
			];
		}
		
		return $return;
	}

    public function searchAfter(array $searchAfter)
    {
        if(empty($searchAfter)) return $this;
        $this->_query['search_after'] = $searchAfter;
        return $this;
    }

	public function debug(bool $status = false)
	{
		$this->_isDebug = $status;
		return $this;
	}
	
	public function disableScroll(bool $status = true)
	{
		$this->_isScroll = false;
		return $this;
	}
	
	public function source(array $sources)
	{
		$this->_query['_source'] = $sources;
		return $this;
	}
	
	public function getQuery()
	{
		return $this->_query;
	}
	
	private function getPath(): string
	{
		if (empty($this->_query['size'])) {
			$path = $this->_source . '/' . $this->_searchSuffix;
			return $path;
		}
		
		if (empty ($this->_scrollId)) {
			$path = $this->_source . '/' . $this->_searchSuffix . ($this->_page > 1 ? '' : ($this->_isScroll ? '?scroll=1m' : ''));
		} else {
			$path = '_search/scroll';
			$this->_query = ['scroll' => '1m', 'scroll_id' => $this->_scrollId];
		}
		return $path;
	}
	
}