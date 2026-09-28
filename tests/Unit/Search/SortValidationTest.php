<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use InvalidArgumentException;
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
}
