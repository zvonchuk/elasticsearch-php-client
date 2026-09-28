# Query Types

Elasticsearch PHP Client provides a fluent API for building Elasticsearch queries. This section covers different types of queries supported by the library.

## Available Query Types

- [Basic Queries](basic.html) - Simple queries like Match All
- [Term Queries](term-queries.html) - Exact matching for structured data
- [Match Queries](match-queries.html) - Text analysis for full-text search
- [Boolean Queries](boolean-queries.html) - Combining multiple queries with logic
- [Geo Queries](geo-queries.html) - Location-based search
- [Compound Queries](compound-queries.html) - dis_max, constant_score and nested
- [Boost and Query Names](common-options.html) - options every query accepts

## Query Builder Pattern

All query types follow a consistent builder pattern, making them easy to use and combine.

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

// Creating a query is as simple as using the appropriate factory method
$query = QueryBuilders::matchQuery('title', 'elasticsearch');

// Many query types support additional options
$query->operator('and')->fuzziness('AUTO');

// Every query can be boosted and named
$query->boost(2)->queryName('title');
```

Browse the sections to learn more about each query type.
