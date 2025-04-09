# Match Queries

Match queries perform analysis on the search term before matching, making them ideal for full-text search.

## Match Query

The standard match query:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::matchQuery('description', 'comfortable office chair');
```

This generates:

```json
{
  "match": {
    "description": {
      "query": "comfortable office chair"
    }
  }
}
```

### Match Query with Options

You can customize the match query with additional options:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::matchQuery('description', 'comfortable office chair')
    ->operator('AND')        // Require all terms to match
    ->fuzziness('AUTO');     // Enable fuzzy matching
```

This generates:

```json
{
  "match": {
    "description": {
      "query": "comfortable office chair",
      "operator": "AND",
      "fuzziness": "AUTO"
    }
  }
}
```

## Match Phrase Query

Matches documents where the terms appear in the exact order specified:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::matchPhraseQuery('description', 'office chair');
```

This generates:

```json
{
  "match_phrase": {
    "description": "office chair"
  }
}
```

## Match Phrase Prefix Query

Similar to match phrase, but allows the last term to be a prefix:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::matchPhrasePrefixQuery('title', 'office ch');
```

This generates:

```json
{
  "match_phrase_prefix": {
    "title": "office ch"
  }
}
```

## Example: Combining Match Queries

Here's an example of combining multiple match queries in a search:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->should(QueryBuilders::matchQuery('title', 'office chair'))
    ->should(QueryBuilders::matchQuery('description', 'office chair')
        ->operator('AND'));

$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);

$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);
```
