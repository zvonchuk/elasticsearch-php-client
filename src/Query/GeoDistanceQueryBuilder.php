<?php

namespace Zvonchuk\Elastic\Query;

class GeoDistanceQueryBuilder extends QueryBuilder
{
    private string $field;
    private ?string $distance = null;
    private ?array $point = null;

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

    public function point(float $lat, float $lon): GeoDistanceQueryBuilder
    {
        $this->point = ['lat' => $lat, 'lon' => $lon];
        return $this;
    }

    public function getSource()
    {
        return [
            $this->name => [
                'distance' => $this->distance,
                $this->field => $this->point,
            ]
        ];
    }
}
