<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

use InvalidArgumentException;
use Zvonchuk\Elastic\Query\MultiMatchQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class MultiMatchQueryTest extends TestCase
{
    public function testMinimal(): void
    {
        self::assertRenders(
            '{"multi_match":{"query":"iphone","fields":["name^3","description"]}}',
            QueryBuilders::multiMatchQuery('iphone', ['name^3', 'description'])->toArray(),
        );
    }

    public function testAllOptions(): void
    {
        $query = QueryBuilders::multiMatchQuery('iphone 15', ['name', 'description'])
            ->type(MultiMatchQueryBuilder::BEST_FIELDS)
            ->operator('and')
            ->fuzziness('AUTO')
            ->prefixLength(1)
            ->tieBreaker(0.3)
            ->minimumShouldMatch('50%')
            ->analyzer('standard')
            ->boost(2)
            ->queryName('text');

        self::assertRenders(
            '{"multi_match":{"query":"iphone 15","fields":["name","description"],"type":"best_fields","operator":"and",'
            . '"fuzziness":"AUTO","prefix_length":1,"tie_breaker":0.3,"minimum_should_match":"50%","analyzer":"standard",'
            . '"boost":2,"_name":"text"}}',
            $query->toArray(),
        );
    }

    public function testUnknownTypeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        QueryBuilders::multiMatchQuery('x', ['a'])->type('best_field');
    }
}
