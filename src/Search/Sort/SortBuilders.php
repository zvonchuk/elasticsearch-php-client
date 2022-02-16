<?php

namespace Zvonchuk\Elastic\Search\Sort;

class SortBuilders
{
    public static function scriptSort(string $script, string $type): ScriptSort
    {
        return new ScriptSort($script, $type);
    }

    public static function fieldSort(string $field): FieldSort
    {
        return new FieldSort($field);
    }

    public static function geoDistanceSort(string $field, float $lat, float $lon): GeoSort
    {
        return new GeoSort($field, $lat, $lon);
    }
}