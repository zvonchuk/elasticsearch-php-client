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

## More Match Options

```php
<?php
use Zvonchuk\Elastic\Query\MatchQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::matchQuery('name', 'guseynov')
    ->operator(MatchQueryBuilder::OPERATOR_AND) // "and" / "or"; anything else is rejected
    ->fuzziness(1)          // 0, 1, 2 or "AUTO"
    ->prefixLength(0)       // allow a typo in the first letter too
    ->maxExpansions(50)     // cap the number of fuzzy variants
    ->minimumShouldMatch('75%')
    ->analyzer('standard');
```

The value may be a string, number or boolean; it is sent with its type.

## Multi Match Query

Searches one text across several fields, optionally boosted per field:

```php
<?php
use Zvonchuk\Elastic\Query\MultiMatchQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::multiMatchQuery('iphone 15', ['name^3', 'description'])
    ->type(MultiMatchQueryBuilder::BEST_FIELDS) // best_fields, most_fields, cross_fields, phrase, phrase_prefix, bool_prefix
    ->operator('and')
    ->tieBreaker(0.3);
```

```json
{
  "multi_match": {
    "query": "iphone 15",
    "fields": ["name^3", "description"],
    "type": "best_fields",
    "operator": "and",
    "tie_breaker": 0.3
  }
}
```
