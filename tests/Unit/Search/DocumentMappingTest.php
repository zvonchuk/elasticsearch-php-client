<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Search;

use UnexpectedValueException;
use Zvonchuk\Elastic\Search\SearchHit;
use Zvonchuk\Elastic\Search\SearchResponse;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class DocumentMappingTest extends TestCase
{
    public function testDocumentsAreBuiltThroughTheConstructor(): void
    {
        $products = $this->response([
            ['name' => 'iPhone', 'price' => 999.5, 'created_at' => '2026-09-01'],
            ['name' => 'Case', 'price' => 10.0, 'created_at' => '2026-09-02', 'ignored' => true],
        ])->documents(Product::class);

        self::assertEquals([
            new Product('iPhone', 999.5, '2026-09-01'),
            new Product('Case', 10.0, '2026-09-02'),
        ], $products);
    }

    public function testMissingOptionalFieldsUseDefaultsAndNull(): void
    {
        $product = $this->response([['name' => 'Case', 'price' => 10.0]])->documents(Product::class)[0];

        self::assertNull($product->createdAt);
        self::assertSame([], $product->tags);
    }

    public function testMissingRequiredFieldIsExplained(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('"price"');
        $this->response([['name' => 'Case']])->documents(Product::class);
    }

    public function testWrongTypeIsExplained(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(Product::class);
        $this->response([['name' => 'Case', 'price' => 'free']])->documents(Product::class);
    }

    public function testMapWithACallable(): void
    {
        $ids = $this->response([['name' => 'a', 'price' => 1.0]])->map(static fn (SearchHit $hit): string => $hit->id . ':' . $hit->source['name']);
        self::assertSame(['0:a'], $ids);
    }

    /** @param list<array<string, mixed>> $sources */
    private function response(array $sources): SearchResponse
    {
        $hits = [];
        foreach ($sources as $i => $source) {
            $hits[] = ['_index' => 'p', '_id' => (string) $i, '_score' => 1.0, '_source' => $source];
        }

        return new SearchResponse(['hits' => ['hits' => $hits]]);
    }
}

final class Product
{
    /** @param list<string> $tags */
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $createdAt,
        public readonly array $tags = [],
    ) {
    }
}
