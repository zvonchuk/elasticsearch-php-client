<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use Zvonchuk\Elastic\Search\CountResponse;
use Zvonchuk\Elastic\Search\SearchResponse;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class ResponsesTest extends TestCase
{
    public function testSearchResponse(): void
    {
        $response = new SearchResponse([
            'took' => 3,
            'timed_out' => false,
            'hits' => [
                'total' => ['value' => 2, 'relation' => 'eq'],
                'max_score' => 1.5,
                'hits' => [
                    ['_index' => 'p', '_id' => '1', '_score' => 1.5, '_source' => ['title' => 'a']],
                    ['_index' => 'p', '_id' => '2', '_score' => 1.0, '_source' => ['title' => 'b']],
                ],
            ],
            'aggregations' => ['avg_price' => ['value' => 10.5]],
        ]);

        self::assertSame(2, $response->getTotal());
        self::assertCount(2, $response->getHits());
        self::assertSame('a', $response->getHits()[0]['_source']['title']);
        self::assertSame(['avg_price' => ['value' => 10.5]], $response->getAggregations());
    }

    public function testSearchResponseWithoutAggregations(): void
    {
        $response = new SearchResponse(['hits' => ['total' => ['value' => 0, 'relation' => 'eq'], 'hits' => []]]);
        self::assertSame([], $response->getAggregations());
    }

    public function testCountResponse(): void
    {
        self::assertSame(7, (new CountResponse(['count' => 7]))->getCount());
    }
}
