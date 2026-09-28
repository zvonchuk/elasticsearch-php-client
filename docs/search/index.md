# Searching

A search is a `SearchRequest` for an index (or alias) with a `SearchSourceBuilder` body.

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

$source = (new SearchSourceBuilder())
    ->query(QueryBuilders::matchQuery('title', 'php'))
    ->from(0)
    ->size(20)
    ->include(['id', 'title']);

$response = $client->search((new SearchRequest('articles'))->source($source));
```

- [Responses](responses.html) — typed hits, matched queries, mapping hits to your own classes
- [Multi search](multi-search.html) — several searches in one round trip

## Search options

| Method | Elasticsearch | Purpose |
|---|---|---|
| `query()` | `query` | the query |
| `from()`, `size()` | `from`, `size` | paging (defaults 0 and 10) |
| `include()`, `exclude()` | `_source` | which source fields to return |
| `sort()` | `sort` | see [Sorting](../sorting/) |
| `aggregation()` | `aggregations` | see [Aggregations](../aggregations/) |
| `searchAfter()` | `search_after` | deep paging after the last hit's sort values |
| `timeout('2s')` | `timeout` | per-shard time limit; partial results come back with `timedOut() === true` |
| `trackTotalHits(false)` | `track_total_hits` | skip counting all hits (faster); `getTotal()` is then `null`. An int counts up to that number |
| `minScore(3.5)` | `min_score` | leave out weak hits |
| `explain()` | `explain` | score explanation per hit (`SearchHit::$explanation`); slow, for debugging |

A `SearchRequest` without a source searches with the defaults; a `CountRequest` without a query counts everything.
