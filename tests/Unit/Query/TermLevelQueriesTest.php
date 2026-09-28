<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class TermLevelQueriesTest extends TestCase
{
    public function testPrefix(): void
    {
        self::assertRenders('{"prefix":{"code":{"value":"AZ"}}}', QueryBuilders::prefixQuery('code', 'AZ')->toArray());
        self::assertRenders(
            '{"prefix":{"code":{"value":"az","case_insensitive":true,"boost":2}}}',
            QueryBuilders::prefixQuery('code', 'az')->caseInsensitive()->boost(2)->toArray(),
        );
    }

    public function testWildcard(): void
    {
        self::assertRenders(
            '{"wildcard":{"passport":{"value":"AZE*67","case_insensitive":true,"_name":"passport"}}}',
            QueryBuilders::wildcardQuery('passport', 'AZE*67')->caseInsensitive()->queryName('passport')->toArray(),
        );
    }

    public function testIds(): void
    {
        self::assertRenders('{"ids":{"values":["1","2"],"_name":"picked"}}', QueryBuilders::idsQuery(['1', '2'])->queryName('picked')->toArray());
    }

    public function testFuzzy(): void
    {
        self::assertRenders(
            '{"fuzzy":{"name":{"value":"guseynov","fuzziness":1,"prefix_length":0,"max_expansions":10,"transpositions":false}}}',
            QueryBuilders::fuzzyQuery('name', 'guseynov')->fuzziness(1)->prefixLength(0)->maxExpansions(10)->transpositions(false)->toArray(),
        );
    }
}
