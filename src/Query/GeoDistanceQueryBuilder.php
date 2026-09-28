<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class GeoDistanceQueryBuilder extends QueryBuilder
{
    private ?string $distance = null;
    /** @var array{lat: float, lon: float}|null */
    private ?array $point = null;

    public function __construct(private readonly string $field)
    {
    }

    public function distance(string $distance): static
    {
        $this->distance = $distance;
        return $this;
    }

    public function point(float $lat, float $lon): static
    {
        $this->point = ['lat' => $lat, 'lon' => $lon];
        return $this;
    }

    public function toArray(): array
    {
        return ['geo_distance' => ['distance' => $this->distance, $this->field => $this->point]];
    }
}
