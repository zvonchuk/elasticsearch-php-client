<?php

namespace Zvonchuk\Elastic\Driver;

use Exception;

class Geo
{
	public function bounding(string $field, array $location): array
	{
		$bounding = [];
		$possibleKeys = ['top_right', 'top_left', 'bottom_right', 'bottom_left'];
		foreach ($location as $key => $value) {
			if (in_array($key, $possibleKeys)) {
				$bounding[$key] = $value;
			}
		}

        return [
            'geo_bounding_box' => [
                $field => $bounding
            ]
        ];
	}
	
	public function distance(string $location, string $distance): array
	{
        return [
            'geo_distance' => [
                'distance' => $distance,
                'location' => $location
            ]
        ];
	}
}