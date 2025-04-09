# Indexing Documents

Indexing is the process of adding documents to an Elasticsearch index.

## Basic Document Indexing

To index a document with a specific ID:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\IndexRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new IndexRequest('products');
$request->id('1');
$request->source([
    'name' => 'Smartphone',
    'description' => 'Latest model with great features',
    'price' => 699.99,
    'category' => 'electronics',
    'in_stock' => true,
    'created_at' => '2023-04-01T10:30:45Z'
]);

$response = $client->index($request);
```

The response contains information about the operation:

```
[
  "_index" => "products",
  "_id" => "1",
  "_version" => 1,
  "result" => "created",
  "_shards" => [
    "total" => 2,
    "successful" => 1,
    "failed" => 0
  ],
  "_seq_no" => 0,
  "_primary_term" => 1
]
```

## Indexing Documents with Complex Fields

You can index documents with nested objects and arrays:

```php
<?php
use Zvonchuk\Elastic\Core\IndexRequest;

$request = new IndexRequest('products');
$request->id('2');
$request->source([
    'name' => 'Laptop',
    'price' => 1299.99,
    'specifications' => [
        'cpu' => 'Intel Core i7',
        'ram' => '16GB',
        'storage' => '512GB SSD'
    ],
    'tags' => ['electronics', 'computer', 'laptop'],
    'variants' => [
        ['color' => 'silver', 'price' => 1299.99],
        ['color' => 'space gray', 'price' => 1349.99]
    ]
]);

$response = $client->index($request);
```

## Indexing Documents with Geo-Points

For location-based applications, you can index documents with geo-point fields:

```php
<?php
use Zvonchuk\Elastic\Core\IndexRequest;

$request = new IndexRequest('stores');
$request->id('1');
$request->source([
    'name' => 'Downtown Store',
    'address' => '123 Main St, New York, NY',
    'location' => [
        'lat' => 40.7128,
        'lon' => -74.0060
    ],
    'opening_hours' => '9:00-18:00'
]);

$response = $client->index($request);
```

## Checking the Result

You can check the response to see if the indexing was successful:

```php
<?php
if ($response['result'] === 'created' || $response['result'] === 'updated') {
    echo "Document {$response['_id']} was successfully indexed.\n";
    echo "Version: {$response['_version']}\n";
} else {
    echo "Error indexing document.\n";
}
```

## Example: Indexing Multiple Documents

Here's how to index multiple documents in a loop:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\IndexRequest;

$client = Client::getInstance(['localhost:9200']);

$products = [
    [
        'id' => '1',
        'name' => 'Smartphone',
        'price' => 699.99
    ],
    [
        'id' => '2',
        'name' => 'Laptop',
        'price' => 1299.99
    ],
    [
        'id' => '3',
        'name' => 'Headphones',
        'price' => 199.99
    ]
];

foreach ($products as $product) {
    $request = new IndexRequest('products');
    $request->id($product['id']);
    unset($product['id']); // Remove ID from source data
    $request->source($product);
    
    $response = $client->index($request);
    echo "Indexed product {$response['_id']}: {$response['result']}\n";
}
```

For bulk indexing of many documents, use the [Bulk API](bulk.html) for better performance.
