<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Query;

use InvalidArgumentException;
use Zvonchuk\Elastic\Query\MatchQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class MatchOptionsTest extends TestCase
{
    public function testAllOptions(): void
    {
        $query = QueryBuilders::matchQuery('name.skeleton', 'Cahangir Əsgərov')
            ->operator(MatchQueryBuilder::OPERATOR_AND)
            ->fuzziness(1)
            ->prefixLength(0)
            ->maxExpansions(20)
            ->analyzer('name_skeleton')
            ->boost(5)
            ->queryName('name_fuzzy');

        self::assertRenders(
            '{"match":{"name.skeleton":{"query":"Cahangir Əsgərov","operator":"and","fuzziness":1,"prefix_length":0,'
            . '"max_expansions":20,"analyzer":"name_skeleton","boost":5,"_name":"name_fuzzy"}}}',
            $query->toArray(),
        );
    }

    public function testMinimumShouldMatchAndAutoFuzziness(): void
    {
        self::assertRenders(
            '{"match":{"t":{"query":"a b c","fuzziness":"AUTO","minimum_should_match":"75%"}}}',
            QueryBuilders::matchQuery('t', 'a b c')->fuzziness('AUTO')->minimumShouldMatch('75%')->toArray(),
        );
    }

    public function testOperatorIsCaseInsensitive(): void
    {
        self::assertSame('AND', QueryBuilders::matchQuery('t', 'x')->operator('AND')->toArray()['match']['t']['operator']);
    }

    public function testUnknownOperatorIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        QueryBuilders::matchQuery('t', 'x')->operator('adn');
    }
}
