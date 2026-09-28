# Changelog

All notable changes to this project are documented here. The project follows [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-09-29

First stable release. Requires PHP 8.1+. See [UPGRADE-1.0.md](UPGRADE-1.0.md) for the breaking changes.

### Fixed
- `FilterBuilder` failed to load with a fatal error, so the filter aggregation never worked; it also sent `"aggregations": null`.
- `date_histogram` without sub-aggregations threw a `TypeError`.
- `terms`, `histogram` and metric aggregations silently dropped sub-aggregations; metrics now reject them with a `LogicException`, as Elasticsearch would.
- `percentiles` without `percents()` sent `0` and returned only the 0th percentile; it now uses the Elasticsearch default set.
- `GeoSort`/`ScriptSort` validation threw `Class "…\Sort\Exception" not found` instead of an `InvalidArgumentException`.
- `GeoSort` rejected its own `NAUTICALMILES` unit and ignored the field name (always `location`).
- Sorts defaulted to descending although the documentation (and Elasticsearch) default to ascending, so `fieldSort('price')` returned the most expensive first and a geo distance sort the farthest first. Field, geo distance and script sorts are now ascending by default; a field sort on `_score` stays descending.
- `geo_distance` ignored the field name (always `location`).
- `bool` sent `mustNot` instead of `must_not` (a warning in Elasticsearch 7, an error in 8).
- `bool`, sub-aggregations, sorts and aggregations captured their children when added; later changes to a child were lost.
- Several sorts were merged into one object, so sorts with the same key (two geo distance sorts, two script sorts, the same field twice) silently replaced each other; `sort` is now sent as a list in the order added.
- `range` bounds accepted only strings (`TypeError` for numbers under `strict_types`); they now take numbers, strings and `DateTimeInterface`.
- `term` and `match` cast values to strings (`true` became `"1"`, rejected on boolean fields).
- `IndexRequest` without id, an empty `BulkRequest`, `CountRequest` without query and `SearchRequest` without source failed on uninitialised properties; requests that need an id now say so with a `LogicException`.
- `SearchResponse::getTotal()` threw a `TypeError` when hits were not tracked; it returns `null`.

### Added
- `boost()` and `queryName()` (`_name`) on every query.
- `bool`: `minimumShouldMatch()`.
- `match`: `prefixLength()`, `maxExpansions()`, `analyzer()`, `minimumShouldMatch()`; integer `fuzziness()`; operator validation.
- Queries: `multi_match`, `dis_max`, `constant_score`, `nested`, `prefix`, `wildcard`, `ids`, `fuzzy`.
- Aggregations: `sum`, `global`.
- `SearchSourceBuilder`: `timeout()`, `trackTotalHits()`, `minScore()`, `explain()`.
- Typed responses: `SearchResponse::hits()` returning `SearchHit` objects (id, index, score, source, matched queries, sort, highlight, explanation), `took()`, `timedOut()`, `failedShards()`, `maxScore()`, `totalRelation()`.
- `SearchResponse::documents(class-string<T>)` and `map()` to turn hits into your own objects, with PHPStan generics.
- Multi search: `MultiSearchRequest`, `Client::msearch()`, `MultiSearchResponse` (failures throw `MultiSearchException`).
- Aliases: `CreateRequest::mappings()` and `alias()`, `UpdateAliasesRequest`, `Indices::updateAliases()`, `existsAlias()`, `indicesForAlias()`, `swapAlias()`.
- `QueryInterface`, `AggregationInterface`, `SortInterface`: builders accept your own implementations.
- Integer document ids in `id()`.
- `Client::create()` and `Client::elasticsearch()`.

### Changed
- `Client` takes an `\Elasticsearch\Client` in its constructor; `Client::getInstance()` is removed.
- `declare(strict_types=1)` and full type declarations throughout; `toArray()` replaces `getSource()`/`getQuery()`, which remain as deprecated aliases.
- PHP 8.1 is the minimum version.
- Tests (unit + integration against Elasticsearch 7.17), PHPStan level 8 and CI on PHP 8.1–8.4.

## [0.2.0] - 2022-08-11
## [0.1.0] - 2021-03-19
