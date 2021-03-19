<?php

namespace Zvonchuk\Elastic\Driver;

use Exception;

/**
 * @method gte (string $key, string $value)
 * @method gt (string $key, string $value)
 * @method lte (string $key, string $value)
 * @method lt (string $key, string $value)
 */
class Range
{
	private $_boost = null;
	
	public function setBoost(float $boost)
	{
		$this->_boost = $boost;
		return $this;
	}
	
	public function __call($term, $arguments)
	{
		list($key, $value) = $arguments;
		if (!in_array($term, ['gte', 'gt', 'lte', 'lt'])) throw new Exception('Incorrect term');
		//if (empty($value)) throw new \Exception('Incorrect value');
		
		$localRequest['range'][$key][$term] = $value;
		
		if (!empty($this->_boost)) $localRequest['range'][$key]['boost'] = $this->_boost;
		return $localRequest;
	}
	
}