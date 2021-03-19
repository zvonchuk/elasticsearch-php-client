<?php

namespace Zvonchuk\Elastic\Driver\Agg\Bucket;

use Zvonchuk\Elastic\Driver\Aggregation;

class TermsBuilder extends Aggregation
{
	private $field = null;
	private $_size = 10;
	
	public function __construct(string $name)
	{
		$this->_name = $name;
	}
	
	public function getSource()
	{
		return [
			$this->_name => [
				'terms' => [
					'field' => $this->field,
					'size' => $this->_size
				]
			]
		];
	}
	
	public function size(int $size): TermsBuilder
	{
		$this->_size = $size;
		return $this;
	}
	
	public function field(string $field): TermsBuilder
	{
		$this->field = $field;
		return $this;
	}
}
