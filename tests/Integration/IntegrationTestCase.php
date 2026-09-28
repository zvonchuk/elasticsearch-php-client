<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Integration;

use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Runs against a live cluster given by ELASTICSEARCH_URL; skipped otherwise.
 * Every test gets its own index, removed afterwards.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected static ElasticsearchClient $elasticsearch;
    protected string $index;

    public static function setUpBeforeClass(): void
    {
        $url = (string) getenv('ELASTICSEARCH_URL');
        if ($url === '') {
            self::markTestSkipped('ELASTICSEARCH_URL is not set');
        }
        self::$elasticsearch = ClientBuilder::create()->setHosts([$url])->build();
    }

    protected function setUp(): void
    {
        $this->index = 'esclient_test_' . strtolower(bin2hex(random_bytes(4)));
    }

    protected function tearDown(): void
    {
        if (isset(self::$elasticsearch)) {
            self::$elasticsearch->indices()->delete(['index' => $this->index . '*', 'ignore_unavailable' => true]);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $documents id => source
     * @param array<string, mixed>                $properties
     */
    protected function seed(array $documents, array $properties = []): void
    {
        self::$elasticsearch->indices()->create([
            'index' => $this->index,
            'body' => $properties === [] ? [] : ['mappings' => ['properties' => $properties]],
        ]);
        $body = [];
        foreach ($documents as $id => $source) {
            $body[] = ['index' => ['_index' => $this->index, '_id' => (string) $id]];
            $body[] = $source;
        }
        if ($body !== []) {
            self::$elasticsearch->bulk(['body' => $body, 'refresh' => true]);
        }
    }
}
