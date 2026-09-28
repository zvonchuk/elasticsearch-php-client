# Term Queries

Term queries are used for exact matching on structured fields. Unlike match queries, they do not perform analysis on the search terms.

## Term Query

Matches documents that contain an exact term in a field:

```php
<?php

use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::termQuery('status', 'active');
```

This generates:

```json
{
  "term": {
    "status": {
      "value": "active"
    }
  }
}
```

## Terms Query

Matches documents that contain one or more exact terms in a field:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::termsQuery('tag', ['urgent', 'important']);
```

This generates:

```json
{
  "terms": {
    "tag": ["urgent", "important"]
  }
}
```

## Range Query

Matches documents with values within a specified range:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::rangeQuery('price')
    ->gte('50.00')
    ->lt('100.00');
```

This generates:

```json
{
  "range": {
    "price": {
      "gte": "50.00",
      "lt": "100.00"
    }
  }
}
```

## Date Range Example

The range query works well with dates too:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::rangeQuery('created_at')
    ->gte('2023-01-01')
    ->lt('now');
```

## Combining Term Queries with Bool Query

You can combine multiple term queries using a bool query:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::termQuery('status', 'active'))
    ->filter(QueryBuilders::rangeQuery('price')->lt('100.00'));
```

## Value Types

`termQuery()` keeps the value's type: `termQuery('active', true)` sends `true`, `termQuery('year', 1950)` sends
`1950`. Range bounds accept numbers, strings and `DateTimeInterface` (sent as ISO 8601):

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::rangeQuery('created_at')->gte(new \DateTimeImmutable('-7 days'))->lt(new \DateTimeImmutable());
```

## Prefix, Wildcard, Ids and Fuzzy Queries

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

QueryBuilders::prefixQuery('code', 'aze')->caseInsensitive();
QueryBuilders::wildcardQuery('passport', 'AZE*67');           // "*" any characters, "?" one; avoid leading "*"
QueryBuilders::idsQuery(['17', '42']);
QueryBuilders::fuzzyQuery('surname', 'guseynov')->fuzziness(1)->prefixLength(0);
```
