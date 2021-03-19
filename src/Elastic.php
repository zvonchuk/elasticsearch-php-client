<?php
/**
 * Copyright © 2019 Yoplastic Team
 *
 * @author Elkin A. Akhundzada (eakhundzade@gmail.com)
 * @author German Zvonchuk (german.zvonchuk@gmail.com)
 *
 * This file is part of Yoplastic project
 * Yoplastic can not be copied and/or distributed without the express
 * permission of Yoplastic team.
 */

namespace Zvonchuk\Elastic;

use Elasticable;
use Zvonchuk\Elastic\Driver\Builder;

class Elastic
{

	use Elasticable;
	
	public $index = NULL;
	protected $mapping_properties = NULL;
	protected $params = NULL;
	private $instance = NULL;
	
	/**
	 * @return mixed
	 */
	public function getType(): string
	{
		return @end(explode('_', $this->index));
	}
	
	public function withYoplastic(bool $force = false)
	{
		if (empty ($this->instance) || true === $force) {
			$this->instance = new Builder($this->index, $this);
		}
		return $this->instance;
	}
	
	public function force(): bool
	{
		$key = array_slice($_SERVER['argv'], 3);
		return (isset($key[0]) && $key[0] == 'force') ? true : false;
	}
	
	
}