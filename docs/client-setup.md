# Client Configuration

The `Client` class is the main entry point for interacting with Elasticsearch. This page explains how to set up and configure the client.

## Singleton Pattern

The client uses a singleton pattern for connection management:

```php
<?php
use Zvonchuk\Elastic\Client;

// Get client instance with a host array
$client = Client::getInstance(['localhost:9200']);
```

## Configuration Options

When connecting to Elasticsearch, you can specify multiple hosts for load balancing and failover:

```php
<?php
$hosts = [
    'elasticsearch1:9200',  // Default protocol is http
    'http://elasticsearch2:9200',
    'https://user:password@elasticsearch3:9200'  // With authentication
];

$client = Client::getInstance($hosts);
```

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
- `search()` - Search for documents
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
```

## Example: Client with Basic Operations

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\GetRequest;

// Initialize client
$client = Client::getInstance(['localhost:9200']);

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
