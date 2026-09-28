<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class GeoBoundingBoxQueryBuilder extends QueryBuilder
{
    private const CORNERS = ['top_left', 'top_right', 'bottom_right', 'bottom_left'];

    /** @var array<string, array<string, float>|string> */
    private array $corners = [];

    public function __construct(private readonly string $field)
    {
    }

    /** @param array<string, float>|string $topLeft point: ['lat' => .., 'lon' => ..], "lat,lon" or geohash */
    public function topLeft(array|string $topLeft): static
    {
        $this->corners['top_left'] = $topLeft;
        return $this;
    }

    /** @param array<string, float>|string $topRight */
    public function topRight(array|string $topRight): static
    {
        $this->corners['top_right'] = $topRight;
        return $this;
    }

    /** @param array<string, float>|string $bottomRight */
    public function bottomRight(array|string $bottomRight): static
    {
        $this->corners['bottom_right'] = $bottomRight;
        return $this;
    }

    /** @param array<string, float>|string $bottomLeft */
    public function bottomLeft(array|string $bottomLeft): static
    {
        $this->corners['bottom_left'] = $bottomLeft;
        return $this;
    }

    /**
     * Renders a geo_bounding_box query from the recognised corner keys of $location, ignoring the rest.
     *
     * @deprecated since 1.0, will be removed in 2.0: it ignores corners set with topLeft() etc., boost() and
     *             queryName(), and returns an array instead of a query. Use the corner setters and toArray().
     *
     * @param array<string, mixed> $location
     * @return array<string, mixed>
     */
    public function bounding(array $location): array
    {
        return ['geo_bounding_box' => [$this->field => array_intersect_key($location, array_flip(self::CORNERS))]];
    }

    public function toArray(): array
    {
        $corners = [];
        foreach (self::CORNERS as $corner) {
            if (isset($this->corners[$corner])) {
                $corners[$corner] = $this->corners[$corner];
            }
        }

        return ['geo_bounding_box' => [$this->field => $corners] + $this->commonOptions()];
    }
}
