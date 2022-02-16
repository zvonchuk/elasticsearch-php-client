<?php

namespace Zvonchuk\Elastic\Query;

class GeoBoundingBoxQueryBuilder extends QueryBuilder
{
    private string $field;
    private array $topLeft = [];
    private array $topRight = [];
    private array $bottomRight = [];
    private array $bottomLeft = [];

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

    public function topRight(array $topRight): self
    {
        $this->topRight = $topRight;
        return $this;
    }

    public function bottomLeft(array $bottomLeft): self
    {
        $this->bottomLeft = $bottomLeft;
        return $this;
    }

    public function topLeft(array $topLeft): self
    {
        $this->topLeft = $topLeft;
        return $this;
    }

    public function bottomRight(array $bottomRight): self
    {
        $this->bottomRight = $bottomRight;
        return $this;
    }

    public function getSource()
    {
        $clauses = [
            'top_right' => 'topRight',
            'top_left' => 'topLeft',
            'bottom_right' => 'bottomRight',
            'bottom_left' => 'bottomLeft'
        ];

        $query = [];
        foreach ($clauses as $key => $variable) {
            if (count($this->{$variable}) > 0) {
                $query[$key] = $this->{$variable};
            }
        }

        return [
            $this->name => [
                $this->field => $query
            ]
        ];
    }
}