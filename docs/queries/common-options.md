# Boost and Query Names

Every query accepts two options that Elasticsearch supports on all query types.

## boost

`boost()` changes how much a query contributes to the score:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::boolQuery()
    ->should(QueryBuilders::matchQuery('title', 'php')->boost(3))
    ->should(QueryBuilders::matchQuery('body', 'php'));
```

## queryName

`queryName()` sets the query's `_name`. Every hit then lists the names of the queries it matched, which tells you
*why* a document was found:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::boolQuery()
    ->should(QueryBuilders::termQuery('status', 'vip')->queryName('vip'))
    ->should(QueryBuilders::rangeQuery('orders')->gte(10)->queryName('frequent'));

foreach ($client->search($request)->hits() as $hit) {
    if ($hit->matched('vip')) {
        // ...
    }
}
```

## Where the options go

The package places them where Elasticsearch expects them for each query type — inside the field object for field
queries, next to the clauses otherwise:

```json
{ "match": { "title": { "query": "php", "boost": 3, "_name": "title" } } }
{ "bool":  { "should": [ ... ], "boost": 2, "_name": "any" } }
```
