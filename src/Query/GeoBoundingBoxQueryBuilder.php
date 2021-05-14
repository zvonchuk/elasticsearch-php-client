<?php

namespace Zvonchuk\Elastic\Query;

class GeoBoundingBoxQueryBuilder extends QueryBuilder
{
    private string $field;
    private ?float $topLeft = null;
    private ?float $bottomRight = null;

    public function __construct(string $field)
    {
        $this->name = 'geo_bounding_box';
        $this->field = $field;
    }

    public function bounding(array $location): array
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
                $this->field => $bounding
            ]
        ];
    }

    public function topLeft(float $topLeft): QueryBuilder
    {
        $this->topLeft = $topLeft;
        return $this;
    }

    public function bottomRight(float $bottomRight): QueryBuilder
    {
        $this->bottomRight = $bottomRight;
        return $this;
    }

    public function getSource()
    {
        return [
            $this->name => [
                $field => [
                    'top_left' => $this->topLeft,
                    'bottom_right' => $this->bottomRight
                ]
            ]
        ];
    }
}