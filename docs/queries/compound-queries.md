# Compound Queries

Besides [bool](boolean-queries.html), three compound queries combine or wrap other queries.

## Dis Max Query

Scores a document by its **best** matching query, plus `tie_breaker` × the scores of the other matching queries.
Useful when the same text is matched several ways and should not be counted several times:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::disMaxQuery()
    ->add(QueryBuilders::matchQuery('name.exact', 'Jahangir Asgarov'))
    ->add(QueryBuilders::matchQuery('name.transliterated', 'Jahangir Asgarov'))
    ->tieBreaker(0.2);
```

```json
{
  "dis_max": {
    "queries": [
      { "match": { "name.exact": { "query": "Jahangir Asgarov" } } },
      { "match": { "name.transliterated": { "query": "Jahangir Asgarov" } } }
    ],
    "tie_breaker": 0.2
  }
}
```

## Constant Score Query

Matches like its filter, but every hit gets the same score — the boost (1.0 by default):

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$bonus = QueryBuilders::constantScoreQuery(QueryBuilders::termQuery('birth_year', 1950))->boost(3);
```

## Nested Query

Searches objects of a `nested` field one by one, so conditions must hold within the same object:

```php
<?php
use Zvonchuk\Elastic\Query\NestedQueryBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::nestedQuery('documents', QueryBuilders::boolQuery()
        ->must(QueryBuilders::termQuery('documents.type', 'passport'))
        ->must(QueryBuilders::termQuery('documents.number', 'AZE1234567')))
    ->scoreMode(NestedQueryBuilder::SCORE_MAX)  // avg (default), max, min, sum, none
    ->ignoreUnmapped();                          // match nothing instead of failing on indices without the field
```
