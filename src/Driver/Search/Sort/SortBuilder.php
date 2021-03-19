<?php

namespace Zvonchuk\Elastic\Driver\Search\Sort;

class SortBuilder
{
    public static function fieldSort(string $field): FieldSort
    {
        return new FieldSort($field);
    }

    public static function geoDistanceSort(string $field, float $lat, float $lon): GeoSort
    {
        return new GeoSort($field, $lat, $lon);
    }
}