<?php

namespace Zvonchuk\Elastic\Query;

class GeoDistanceQueryBuilder extends QueryBuilder
{
    private string $field;
    private ?string $distance = null;
    private ?string $point = null;

    public function __construct(string $field)
    {
        $this->name = 'geo_distance';
        $this->field = $field;
    }

    public function distance(string $distance): GeoDistanceQueryBuilder
    {
        $this->distance = $distance;
        return $this;
    }

    public function point($lat, $lon): GeoDistanceQueryBuilder
    {
        $this->location = $lat . ',' . $lon;
        return $this;
    }

    public function getSource()
    {
        return [
            $this->name => [
                'distance' => $this->distance,
                'location' => $this->location
            ]
        ];
    }
}