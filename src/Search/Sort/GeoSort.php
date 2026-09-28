<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Sort;

class GeoSort extends SortBuilder
{
    public const INCH = 'in';
    public const YARD = 'yd';
    public const FEET = 'ft';
    public const KILOMETERS = 'km';
    public const NAUTICALMILES = 'nmi';
    public const MILLIMETERS = 'mm';
    public const CENTIMETERS = 'cm';
    public const MILES = 'mi';
    public const METERS = 'm';

    private const UNITS = [
        self::INCH, self::YARD, self::FEET, self::KILOMETERS, self::NAUTICALMILES, 'NM',
        self::MILLIMETERS, self::CENTIMETERS, self::MILES, self::METERS,
    ];

    private string $unit = self::METERS;

    public function __construct(private readonly string $field, private readonly float $lat, private readonly float $lon)
    {
        $this->order = self::ASC; // closest first, as in Elasticsearch
    }

    public function unit(string $unit): static
    {
        if (!in_array($unit, self::UNITS, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown distance unit "%s"; expected one of: %s.',
                $unit,
                implode(', ', self::UNITS),
            ));
        }
        $this->unit = $unit;

        return $this;
    }

    public function toArray(): array
    {
        return [
            '_geo_distance' => [
                $this->field => ['lat' => $this->lat, 'lon' => $this->lon],
                'order' => $this->order,
                'unit' => $this->unit,
            ],
        ];
    }
}
