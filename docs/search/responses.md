# Search Responses

`Client::search()` returns a `SearchResponse`.

## Typed hits

```php
<?php
foreach ($response->hits() as $hit) {
    $hit->id;             // string
    $hit->index;          // string
    $hit->score;          // ?float
    $hit->source;         // array
    $hit->matchedQueries; // list<string> — names given with queryName()
    $hit->sort;           // sort values, for searchAfter()
    $hit->highlight;      // array
    $hit->explanation;    // ?array, with explain()
    $hit->matched('vip'); // bool
}
```

## Response metadata

| Method | Returns |
|---|---|
| `getTotal()` | `?int` — number of matches; `null` when `trackTotalHits(false)` |
| `totalRelation()` | `"eq"` exact, `"gte"` lower bound, or `null` |
| `maxScore()` | `?float` |
| `took()` | `?int` milliseconds |
| `timedOut()` | `bool` — `true` means the hits are partial |
| `failedShards()` | `int` — documents on failed shards are missing |
| `getAggregations()` | `array` |
| `getHits()`, `toArray()` | the raw hits / raw response |

Check `timedOut()` and `failedShards()` whenever "nothing found" leads to a decision.

## Mapping hits to your classes

`documents()` builds one object per hit through the class constructor. Parameters are filled from the source field
with the same name or its snake_case form; missing fields use the parameter default or `null`:

```php
<?php
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $createdAt, // from "created_at"
    ) {
    }
}

/** @var list<Product> $products */
$products = $response->documents(Product::class);
```

A required field that is missing, or a value of the wrong type, throws an `UnexpectedValueException` naming the
field. For mappings that need more than the source, use `map()`:

```php
<?php
use Zvonchuk\Elastic\Search\SearchHit;

$rows = $response->map(fn (SearchHit $hit) => ['id' => $hit->id, 'score' => $hit->score] + $hit->source);
```

Both methods carry PHPStan generics, so static analysis knows the element type.
