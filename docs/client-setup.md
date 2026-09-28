# Client Configuration

`Zvonchuk\Elastic\Client` wraps the official `elasticsearch/elasticsearch` 7.x client. You build and configure
the official client (hosts, credentials, TLS, retries, logging) and hand it over; the wrapper adds the typed
request/response API on top.

## Using a client you configured

```php
<?php
use Elasticsearch\ClientBuilder;
use Zvonchuk\Elastic\Client;

$elasticsearch = ClientBuilder::create()
    ->setHosts(['https://es.example.com:9243'])
    ->setBasicAuthentication('user', 'secret')
    ->setRetries(1)
    ->build();

$client = new Client($elasticsearch);
```

This is the recommended setup in applications that already have a dependency container: register the official
client once and inject `Client` wherever searches are made. In tests, pass a mock of `\Elasticsearch\Client`.

## Quick setup from a host list

For scripts and simple cases `Client::create()` builds the official client with default settings:

```php
<?php
use Zvonchuk\Elastic\Client;

$client = Client::create([
    'elasticsearch1:9200',                      // http by default
    'https://user:password@elasticsearch2:9243', // credentials in the URL
]);
```

Every call creates an independent client, so two clusters can be used side by side:

```php
<?php
use Zvonchuk\Elastic\Client;

$primary = Client::create(['http://primary:9200']);
$archive = Client::create(['http://archive:9200']);
```

## Reaching the official client

For APIs this package does not wrap, use the official client directly:

```php
<?php
$health = $client->elasticsearch()->cluster()->health();
```

> Upgrading from 0.x: `Client::getInstance()` is gone — see [UPGRADE-1.0](https://github.com/zvonchuk/elasticsearch-php-client/blob/master/UPGRADE-1.0.md).

## Available Operations

Once you have the client instance, you can perform various operations:

### Document Operations
- `index()` - Index a document
- `get()` - Retrieve a document
- `update()` - Update a document
- `delete()` - Delete a document
- `exists()` - Check if a document exists
- `bulk()` - Perform bulk operations

### Search Operations
- `search()` - Search for documents ([responses](search/responses.html))
- `msearch()` - Several searches in one round trip ([multi search](search/multi-search.html))
- `count()` - Count documents matching a query

### Index Operations
Through the `indices()` method:

```php
<?php
// Access the indices API
$indices = $client->indices();

// Available operations:
// - exists()
// - create()
// - delete()
// - refresh()
// - getMapping()
// - putMapping()
// - updateAliases(), existsAlias(), indicesForAlias(), swapAlias()
```

## Example: Client with Basic Operations

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\GetRequest;

// Initialize client
$client = Client::create(['localhost:9200']);

// Index a document
$indexRequest = new IndexRequest('products');
$indexRequest->id('1');
$indexRequest->source([
    'name' => 'Smartphone',
    'price' => 699.99,
    'in_stock' => true
]);
$indexResponse = $client->index($indexRequest);

// Get the document
$getRequest = new GetRequest('products');
$getRequest->id('1');
$document = $client->get($getRequest);

print_r($document);
```
