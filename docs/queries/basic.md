# Basic Queries

Basic queries are the simplest type of queries in Elasticsearch. 

## Match All Query

The match all query simply matches all documents in the index:

```php
<?php

use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::matchAllQuery();
```

This generates:

```json
{
  "match_all": {}
}
```

## Exists Query

Checks if a field exists in the document:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::existsQuery('email');
```

This generates:

```json
{
  "exists": {
    "field": "email"
  }
}
```

## Using Basic Queries in a Search

Here's how to use these basic queries in a search:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

// Match all documents
$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::matchAllQuery());
$searchSource->size(10);

$request = new SearchRequest('my_index');
$request->source($searchSource);
$response = $client->search($request);
```
