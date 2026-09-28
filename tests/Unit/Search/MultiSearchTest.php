<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use Zvonchuk\Elastic\Core\MultiSearchRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\MultiSearchException;
use Zvonchuk\Elastic\Search\MultiSearchResponse;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class MultiSearchTest extends TestCase
{
    public function testRequestBody(): void
    {
        $request = (new MultiSearchRequest())
            ->add((new SearchRequest('kyc'))->source((new SearchSourceBuilder())->query(QueryBuilders::termQuery('a', 1))->size(50)))
            ->add(new SearchRequest('hms'));

        self::assertRenders(
            '{"body":[{"index":"kyc"},{"query":{"term":{"a":{"value":1}}},"size":50,"from":0},{"index":"hms"},{"size":10,"from":0}]}',
            $request->toArray(),
        );
        self::assertFalse($request->isEmpty());
        self::assertTrue((new MultiSearchRequest())->isEmpty());
    }

    public function testResponsesInOrder(): void
    {
        $response = new MultiSearchResponse(['responses' => [
            ['hits' => ['total' => ['value' => 1, 'relation' => 'eq'], 'hits' => [['_index' => 'kyc', '_id' => 'k1', '_score' => 1.0]]]],
            ['hits' => ['total' => ['value' => 0, 'relation' => 'eq'], 'hits' => []]],
        ]]);

        self::assertCount(2, $response);
        self::assertSame('k1', $response->get(0)->hits()[0]->id);
        self::assertSame(0, $response->get(1)->getTotal());
        self::assertCount(2, $response->all());
        self::assertSame([], $response->failures());
    }

    public function testAFailedSearchIsNotAnEmptyResult(): void
    {
        $error = ['root_cause' => [['type' => 'index_not_found_exception', 'reason' => 'no such index [hms]']], 'type' => 'index_not_found_exception'];
        $response = new MultiSearchResponse(['responses' => [
            ['hits' => ['hits' => []]],
            ['error' => $error, 'status' => 404],
        ]]);

        self::assertSame([1 => $error], $response->failures());
        self::assertSame([], $response->get(0)->hits());

        try {
            $response->all();
            self::fail('all() must throw when a search failed');
        } catch (MultiSearchException $e) {
            self::assertSame(1, $e->position);
            self::assertStringContainsString('no such index [hms]', $e->getMessage());
        }
    }
}
