<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\ScriptSort;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class SortAndSourceTest extends TestCase
{
    public function testFieldSort(): void
    {
        self::assertRenders('{"price":"asc"}', SortBuilders::fieldSort('price')->order(SortBuilder::ASC)->getSource());
    }

    public function testScriptSort(): void
    {
        self::assertRenders(
            '{"_script":{"order":"desc","type":"number","script":{"lang":"painless","source":"doc[\'a\'].value * 2"}}}',
            SortBuilders::scriptSort("doc['a'].value * 2", ScriptSort::NUMBER)->getSource(),
        );
    }

    public function testDefaultSourceHasPaging(): void
    {
        self::assertRenders('{"size":10,"from":0}', (new SearchSourceBuilder())->getQuery());
    }

    public function testFullSource(): void
    {
        $source = (new SearchSourceBuilder())
            ->query(QueryBuilders::termQuery('status', 'active'))
            ->aggregation(AggregationBuilders::avg('avg_price')->field('price'))
            ->sort(SortBuilders::fieldSort('price'))
            ->from(20)
            ->size(5)
            ->include(['id', 'title'])
            ->exclude(['body'])
            ->searchAfter([100, 'x']);

        self::assertRenders(
            '{"query":{"term":{"status":{"value":"active"}}},"aggregations":{"avg_price":{"avg":{"field":"price"}}},'
            . '"size":5,"from":20,"_source":{"includes":["id","title"],"excludes":["body"]},"sort":{"price":"desc"},'
            . '"search_after":[100,"x"]}',
            $source->getQuery(),
        );
    }
}
