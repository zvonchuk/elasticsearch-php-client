<?php

// Analysed by PHPStan only (see phpstan.neon.dist): checks the generic return types of the response API.

declare(strict_types=1);

use Zvonchuk\Elastic\Search\SearchHit;
use Zvonchuk\Elastic\Search\SearchResponse;

use function PHPStan\Testing\assertType;

final class TypesProduct
{
    public function __construct(public readonly string $name)
    {
    }
}

function checkResponseTypes(SearchResponse $response): void
{
    assertType('list<TypesProduct>', $response->documents(TypesProduct::class));
    assertType('list<string>', $response->map(static fn (SearchHit $hit): string => $hit->id));
    assertType('list<Zvonchuk\Elastic\Search\SearchHit>', $response->hits());
    assertType('int|null', $response->getTotal());
}
