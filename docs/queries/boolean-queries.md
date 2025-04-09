# Boolean Queries

The bool query lets you combine multiple query clauses using Boolean logic. It has four types of clauses:

- `must`: Clauses that must match (AND)
- `should`: Clauses that should match (OR)
- `mustNot`: Clauses that must not match (NOT)
- `filter`: Like must, but in filter context (no score calculation)

## Basic Structure

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(/* query */)
    ->should(/* query */)
    ->mustNot(/* query */)
    ->filter(/* query */);
```

## Example: AND Logic with must

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('title', 'office'))
    ->must(QueryBuilders::matchQuery('description', 'comfortable'));
```

This generates:

```json
{
  "bool": {
    "must": [
      { "match": { "title": { "query": "office" } } },
      { "match": { "description": { "query": "comfortable" } } }
    ]
  }
}
```

## Example: OR Logic with should

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->should(QueryBuilders::matchQuery('title', 'chair'))
    ->should(QueryBuilders::matchQuery('title', 'desk'));
```

## Example: NOT Logic with mustNot

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('category', 'furniture'))
    ->mustNot(QueryBuilders::termQuery('status', 'discontinued'));
```

## Example: Filter Context

Filter context is used when you want to include a condition but don't care about scoring:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('description', 'comfortable chair'))
    ->filter(QueryBuilders::rangeQuery('price')->lte('200.00'))
    ->filter(QueryBuilders::termQuery('in_stock', true));
```

## Complex Boolean Query Example

Boolean queries can be nested to create complex query logic:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(
        QueryBuilders::boolQuery()
            ->should(QueryBuilders::matchQuery('title', 'chair'))
            ->should(QueryBuilders::matchQuery('title', 'stool'))
    )
    ->filter(QueryBuilders::rangeQuery('price')->lte('100.00'))
    ->mustNot(QueryBuilders::termQuery('discontinued', true));
```

## Using Boolean Queries in a Search

Here's how to use a bool query in a search:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('category', 'electronics'))
    ->filter(QueryBuilders::rangeQuery('price')->gte('100')->lte('500'))
    ->mustNot(QueryBuilders::termsQuery('brand', ['unknown', 'generic']));

$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);

$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);
```
