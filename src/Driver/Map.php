<?php

namespace Zvonchuk\Elastic\Driver;

use Zvonchuk\Elastic\Driver\Search\Sort\GeoSort;
use Zvonchuk\Elastic\Driver\Search\Sort\SortBuilder;
use Yoxla\Place\Place;
use Yoxla\Poi\Poi;

class Map
{
	// term - Returns documents that contain an exact term in a provided field.
	// terms - Returns documents that contain one or more exact terms in a provided field.
	private $mapper = [
		'amenity',
		'infrastructure.type_id',
	];
	
	public function getMapper(): array
	{
		return $this->mapper;
	}
	
	public function externalQuery(array $queries, $instance)
	{
		$possibleTerms = ['exact', 'range', 'poi', 'place', 'scroll'];


		if (is_iterable($queries)) {
			
			foreach ($queries as $term => $request) {
				
				if (!in_array($term, $possibleTerms)) continue;
				
				switch ($term) {
					case 'exact':
						foreach ($request as $key => $values) {
							
							if (is_array($values)) {
								
								if ($this->getOperator($key)) {
									
									foreach ($values as $term => $value) {
										$instance->must((new Query())->term($key, $value));
									}
									
								} else {
									$instance->must((new Query())->terms($key, $values));
								}
								
							} else {
								$instance->must((new Query())->term($key, $values));
							}
						}
						break;
					case 'range':
						foreach ($request as $key => $values) {
							foreach ($values as $term => $value) {
								$instance->must((new Range())->{$term}($key, $value));
							}
						}
						break;
                    case 'scroll':
                        $scroll = json_decode(base64_decode($request));
                        if($scroll) {
                            $instance->searchAfter($scroll);
                        }
                        break;
					case 'poi':

						$poi = (new Poi())::findFirst($request);
						if ($poi) {

							$location = $poi->latitude . ',' . $poi->longitude;
							$distance = (new Geo())->distance($location, '0.5km');
                            $sort = SortBuilder::geoDistanceSort('location', $poi->latitude, $poi->longitude)->unit(GeoSort::METERS)->order('asc');
							$instance->filter($distance);
							$instance->sort($sort);

							// Housing_cooperative
							if ($poi->type_id == 9) {

                                $match = (new Text())
                                    ->setOperator('and')
                                    ->setFuzziness("")
                                    ->match('description', $poi->name);

                                $instance->must($match);

                                /*$additional = json_decode($poi->additional);
                                foreach ($additional as $key => $values) {
                                    $queryKey = is_array($values) ? 'terms' : 'term';
                                    $instance->must((new Query())->{$queryKey}($key, $values));
                                }*/

								//$instance->must((new Query())->term('mtk_id', $request));

							} else {

							}
						}
						
						break;
					case 'place':
						$handler = new Place();
						$place = $handler::findFirst($request);
						if ($place instanceof Place) {
							$key = $handler->getKeyById($place->type_id);
							$instance->must((new Query())->term($key, $request));
						}
				}
			}
		}

		return $instance;
	}
	
	public function getOperator(string $key): bool
	{
		return in_array($key, $this->mapper) ? true : false;
	}
}