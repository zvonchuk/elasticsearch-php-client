# Getting Documents

Retrieving documents from Elasticsearch by their ID.

## Basic Document Retrieval

To get a document by its ID:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\GetRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new GetRequest('products');
$request->id('1');

$document = $client->get($request);
```

The response contains the document along with metadata:

```
[
  "_index" => "products",
  "_id" => "1",
  "_version" => 1,
  "_seq_no" => 0,
  "_primary_term" => 1,
  "found" => true,
  "_source" => [
    "name" => "Smartphone",
    "price" => 699.99,
    "in_stock" => true
  ]
]
```

## Checking If a Document Exists

To check if a document exists without retrieving its content:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\ExistsRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new ExistsRequest('products');
$request->id('1');

$exists = $client->exists($request);

if ($exists) {
    echo "Document exists!";
} else {
    echo "Document does not exist.";
}
```

## Handling Missing Documents

When getting a document that doesn't exist, Elasticsearch returns a "not found" response:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\GetRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new GetRequest('products');
$request->id('non_existent_id');

$document = $client->get($request);

if (isset($document['found']) && $document['found'] === true) {
    echo "Document found: ";
    print_r($document['_source']);
} else {
    echo "Document not found!";
}
```

## Processing Retrieved Documents

Here's how to process a retrieved document:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\GetRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new GetRequest('products');
$request->id('1');

$document = $client->get($request);

if (isset($document['found']) && $document['found'] === true) {
    $source = $document['_source'];
    
    echo "Product: {$source['name']}\n";
    echo "Price: \${$source['price']}\n";
    
    if (isset($source['in_stock']) && $source['in_stock'] === true) {
        echo "Status: In Stock\n";
    } else {
        echo "Status: Out of Stock\n";
    }
    
    if (isset($source['specifications'])) {
        echo "Specifications:\n";
        foreach ($source['specifications'] as $key => $value) {
            echo "- $key: $value\n";
        }
    }
} else {
    echo "Product not found!\n";
}
```

## Example: Getting Multiple Documents in a Loop

Here's how to retrieve multiple documents in a loop:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\GetRequest;

$client = Client::getInstance(['localhost:9200']);

$productIds = ['1', '2', '3'];
$foundProducts = [];

foreach ($productIds as $id) {
    $request = new GetRequest('products');
    $request->id($id);
    
    $document = $client->get($request);
    
    if (isset($document['found']) && $document['found'] === true) {
        $foundProducts[] = $document['_source'];
        echo "Retrieved product {$id}: {$document['_source']['name']}\n";
    } else {
        echo "Product {$id} not found\n";
    }
}

echo "Retrieved " . count($foundProducts) . " products total.";
```

For retrieving many documents at once, consider using the [multi-get API](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-multi-get.html) or a [search query](../queries/) with a terms query.
