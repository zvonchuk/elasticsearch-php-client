<?php

namespace Zvonchuk\Elastic\Driver;
class Limit
{
	const PAGE_LIMIT = 20;
	
	static public function getValue(int $page = 1, int $limit = null)
	{
		if (is_null($limit)) $limit = self::PAGE_LIMIT;
		
		return [
			($page - 1) * $limit,
			$limit,
		];
	}
}