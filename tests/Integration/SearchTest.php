<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Integration;

use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\CountRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

final class SearchTest extends IntegrationTestCase
{
    public function testSearchAndCountThroughTheClient(): void
    {
        $this->seed([
            '1' => ['title' => 'elasticsearch in action', 'status' => 'active'],
            '2' => ['title' => 'php in action', 'status' => 'draft'],
        ], ['status' => ['type' => 'keyword']]);

        $client = Client::getInstance([(string) getenv('ELASTICSEARCH_URL')]);

        $query = QueryBuilders::boolQuery()
            ->must(QueryBuilders::matchQuery('title', 'action'))
            ->filter(QueryBuilders::termQuery('status', 'active'));
        $response = $client->search((new SearchRequest($this->index))->source((new SearchSourceBuilder())->query($query)));

        self::assertSame(1, $response->getTotal());
        self::assertSame('1', $response->getHits()[0]['_id']);
        self::assertSame(2, $client->count((new CountRequest($this->index))->query(QueryBuilders::matchAllQuery()))->getCount());
    }
}
