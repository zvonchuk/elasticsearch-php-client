<?php

namespace Zvonchuk\Elastic\Search\Sort;

class GeoSort extends SortBuilder
{
    private float $lat;
    private float $lon;
    private string $unit = self::METERS;

    public const INCH = "in";
    public const YARD = "yd";
    public const FEET = "ft";
    public const KILOMETERS = "km";
    public const NAUTICALMILES = "nmi";
    public const MILLIMETERS = "mm";
    public const CENTIMETERS = "cm";
    public const MILES = "mi";
    public const METERS = "m";

    public function __construct(string $field, float $lat, float $lon)
    {
        $this->field = $field;
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public function unit(string $unit): GeoSort
    {
        $keys = [
            self::INCH, self::YARD, self::FEET, self::KILOMETERS, self::NAUTICALMILES, 'NM',
            self::MILLIMETERS, self::CENTIMETERS, self::MILES, self::METERS,
        ];

        if (!in_array($unit, $keys, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown distance unit "%s"; expected one of: %s.',
                $unit,
                implode(', ', $keys),
            ));
        }
        $this->unit = $unit;

        return $this;
    }

    public function getSource()
    {
        return [
            '_geo_distance' => [
                $this->field => [
                    'lat' => $this->lat,
                    'lon' => $this->lon,
                ],
                'order' => $this->order,
                'unit' => $this->unit,
            ]
        ];
    }
}