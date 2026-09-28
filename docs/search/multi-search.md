# Multi Search

`Client::msearch()` sends several searches in one HTTP round trip (`_msearch`).

```php
<?php
use Zvonchuk\Elastic\Core\MultiSearchRequest;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

$source = (new SearchSourceBuilder())->query(QueryBuilders::matchQuery('name', 'Jahangir Asgarov'))->size(50);

$response = $client->msearch((new MultiSearchRequest())
    ->add((new SearchRequest('sanctions'))->source($source))
    ->add((new SearchRequest('pep'))->source($source)));

$sanctions = $response->get(0); // SearchResponse, in the order added
$pep = $response->get(1);
```

## Failures are not empty results

If one of the searches fails (missing index, bad query), `get()` for it and `all()` throw a
`MultiSearchException` with its position and the Elasticsearch reason, instead of returning an empty result:

```php
<?php
use Zvonchuk\Elastic\Search\MultiSearchException;

try {
    foreach ($response->all() as $i => $result) {
        // ...
    }
} catch (MultiSearchException $e) {
    // $e->position, $e->error
}

$response->failures(); // [position => error], empty when all succeeded
```

An empty `MultiSearchRequest` is not sent.
