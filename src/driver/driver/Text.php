<?php

namespace Zvonchuk\Elastic\Driver;

use Exception;

class Text
{
	private $_operator = null;
	private $_fuzziness = null;
	private $_boost = null;
	
	public function __set($name, $value)
	{
		if ($name == '_fuzziness' && $value == true) $this->_fuzziness = 'AUTO:3,7';
	}
	
	public function setOperator(string $operator): Text
	{
		if (!in_array($operator, ['and', 'or'])) throw new Exception('Incorrect operator');
		$this->_operator = $operator;
		return $this;
	}
	
	public function setFuzziness(string $fuzziness): Text
	{
		$this->_fuzziness = 'AUTO:3,7';
		return $this;
	}
	
	public function setBoost(float $boost): Text
	{
		$this->_boost = $boost;
		return $this;
	}
	
	public function matchPhrasePrefix(string $key, string $query)
	{
		if (empty($key)) throw new Exception('Key not specified');
		
		$localRequest = [];
		$localRequest['match_phrase_prefix'][$key] = [
			'query' => $query,
		];
		
		if (!empty($this->_boost)) $localRequest['match_phrase_prefix'][$key]['boost'] = $this->_boost;
		
		return $localRequest;
		
	}
	
	public function match(string $key, string $query)
	{
		if (empty($key)) throw new Exception('Key not specified');
		
		$localRequest = [];
		$localRequest['match'][$key] = [
			'query' => $query,
		];
		
		if (!empty($this->_operator)) $localRequest['match'][$key]['operator'] = $this->_operator;
		if (!empty($this->_boost)) $localRequest['match'][$key]['boost'] = $this->_boost;
		if (!empty($this->_fuzziness)) $localRequest['match'][$key]['fuzziness'] = $this->_fuzziness;
		
		return $localRequest;
	}
	
}