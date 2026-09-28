<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use InvalidArgumentException;
use Zvonchuk\Elastic\Search\Sort\GeoSort;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class SortValidationTest extends TestCase
{
    public function testGeoSortRejectsUnknownUnit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"parsec"');
        SortBuilders::geoDistanceSort('pin', 40.4, 49.8)->unit('parsec');
    }

    public function testScriptSortRejectsUnknownType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"date"');
        SortBuilders::scriptSort("doc['a'].value", 'date');
    }

    public function testGeoSortAcceptsItsOwnUnitConstants(): void
    {
        foreach ([GeoSort::INCH, GeoSort::YARD, GeoSort::FEET, GeoSort::KILOMETERS, GeoSort::NAUTICALMILES,
                  GeoSort::MILLIMETERS, GeoSort::CENTIMETERS, GeoSort::MILES, GeoSort::METERS, 'NM'] as $unit) {
            $source = SortBuilders::geoDistanceSort('pin', 40.4, 49.8)->unit($unit)->getSource();
            self::assertSame($unit, $source['_geo_distance']['unit']);
        }
    }

    public function testGeoSortUsesTheGivenField(): void
    {
        self::assertRenders(
            '{"_geo_distance":{"pin":{"lat":40.4,"lon":49.8},"order":"asc","unit":"km"}}',
            SortBuilders::geoDistanceSort('pin', 40.4, 49.8)->order(SortBuilder::ASC)->unit(GeoSort::KILOMETERS)->getSource(),
        );
    }
}
