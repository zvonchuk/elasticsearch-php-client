<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use Zvonchuk\Elastic\Search\SearchResponse;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class TypedHitsTest extends TestCase
{
    public function testHitsAndMetadata(): void
    {
        $response = new SearchResponse([
            'took' => 4,
            'timed_out' => true,
            '_shards' => ['total' => 3, 'successful' => 2, 'failed' => 1],
            'hits' => [
                'total' => ['value' => 10000, 'relation' => 'gte'],
                'max_score' => 12.5,
                'hits' => [[
                    '_index' => 'kyc',
                    '_id' => 'kyc-101',
                    '_score' => 12.5,
                    '_source' => ['firstname' => 'Jahangir'],
                    'matched_queries' => ['name_translit', 'dob_full'],
                    'sort' => [12.5, 'kyc-101'],
                    'highlight' => ['firstname' => ['<em>Jahangir</em>']],
                ]],
            ],
        ]);

        $hit = $response->hits()[0];
        self::assertSame('kyc', $hit->index);
        self::assertSame('kyc-101', $hit->id);
        self::assertSame(12.5, $hit->score);
        self::assertSame(['firstname' => 'Jahangir'], $hit->source);
        self::assertSame(['name_translit', 'dob_full'], $hit->matchedQueries);
        self::assertTrue($hit->matched('dob_full'));
        self::assertFalse($hit->matched('passport'));
        self::assertSame([12.5, 'kyc-101'], $hit->sort);
        self::assertSame(['firstname' => ['<em>Jahangir</em>']], $hit->highlight);

        self::assertSame(4, $response->took());
        self::assertTrue($response->timedOut());
        self::assertSame(1, $response->failedShards());
        self::assertSame(10000, $response->getTotal());
        self::assertSame('gte', $response->totalRelation());
        self::assertSame(12.5, $response->maxScore());
    }

    public function testMinimalHit(): void
    {
        $response = new SearchResponse(['hits' => ['max_score' => null, 'hits' => [['_index' => 'p', '_id' => '1', '_score' => null]]]]);

        $hit = $response->hits()[0];
        self::assertNull($hit->score);
        self::assertSame([], $hit->source);
        self::assertSame([], $hit->matchedQueries);
        self::assertNull($response->maxScore());
        self::assertNull($response->took());
        self::assertFalse($response->timedOut());
        self::assertSame(0, $response->failedShards());
    }
}
