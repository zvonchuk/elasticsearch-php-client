<?php

namespace Zvonchuk\Elastic\Driver;

use Exception;
use stdClass;

class Query
{
	private $_boost = null;
	
	public function term(string $key, $value): array
	{
		$localRequest['term'][$key] = [
			'value' => $value
		];
		
		if (!empty($this->_boost)) $localRequest['term'][$key]['boost'] = $this->_boost;
		
		return $localRequest;
	}
	
	public function terms(string $key, array $value): array
	{
		$localRequest['terms'][$key] = $value;
		
		return $localRequest;
	}
	
	public function setBoost(float $boost)
	{
		$this->_boost = $boost;
		return $this;
	}


    /**
     * @param string $key
     * @param string $direction
     * @return string[]
     * @throws Exception
     */
    public function sort(string $key, string $direction)
	{
		if (!in_array($direction, ['asc', 'desc'])) throw new Exception('Incorrect statement');

        return [
            $key => $direction
        ];
	}
	
	public function nested(string $key, array $query): array
	{
		return
			[
				'nested' =>
					[
						'path' => $key,
						'query' => $query,
						'inner_hits' => new stdClass()
					]
			];
	}
}