# Upgrading from 0.x to 1.0

1.0 requires **PHP 8.1** and keeps the query-building API. Most code only needs the client change below.

## 1. Create the client without `getInstance()`

The singleton is gone: the first `getInstance()` call used to fix the hosts for the whole process.

```php
use Zvonchuk\Elastic\Client;

// 0.x
$client = Client::getInstance(['localhost:9200']);

// 1.0 — same hosts, a new independent client
$client = Client::create(['localhost:9200']);

// 1.0 — or wrap an official client you configured (TLS, credentials, retries, logger)
$client = new Client(\Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build());
```

## 2. Behaviour that changed because it was a bug

Check these if your code relied on them:

| 0.x | 1.0 |
|---|---|
| `geo_distance` and geo distance sort used a field named `location` whatever you passed | the field you pass is used |
| geo distance sort defaulted to farthest first | closest first (`order(SortBuilder::DESC)` for the old order) |
| `termQuery('active', true)` sent `"1"` | sends `true`; numbers are sent as numbers |
| `bool` sent `mustNot` | sends `must_not` |
| `sort` was one merged object (`{"price":"desc","_script":…}`), duplicate keys lost | a list of sort objects in the order added |
| `percentiles` without `percents()` returned the 0th percentile | Elasticsearch default set |
| sub-aggregations under `terms`/`histogram` were dropped | they are sent |
| `subAggregation()` on a metric (avg, sum, stats, …) was silently ignored | throws `LogicException` |
| `getTotal()` crashed when hits were not tracked | returns `null` |

## 3. Signatures

- Fluent setters return `static`; builders are typed (`int|float|bool|string` values, `int|float|string|DateTimeInterface` range bounds).
- `getSource()` / `getQuery()` still work but are deprecated: use `toArray()`.
- `BulkRequest::add()` accepts `IndexRequest|UpdateRequest|DeleteRequest`; `BulkRequest` no longer extends `Request`.
- `GeoDistanceQueryBuilder::point()` takes floats; geo bounding box corners take one point each (`['lat' => .., 'lon' => ..]`, `"lat,lon"` or a geohash).
- Custom subclasses: `QueryBuilder::$name`, `SortBuilder::$field` and the underscore-prefixed aggregation properties are gone; implement `toArray()` (or the new interfaces) instead of `getSource()`.
