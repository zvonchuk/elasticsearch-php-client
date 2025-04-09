# Bulk Operations

The Bulk API allows you to perform multiple document operations (index, update, delete) in a single request, which is much more efficient than individual requests.

## Basic Bulk Operation

To create a bulk request with multiple operations:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\IndexRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;

$client = Client::getInstance(['localhost:9200']);

// Create a bulk request
$bulkRequest = new BulkRequest();

// Add an index operation
$indexRequest = new IndexRequest('products');
$indexRequest->id('1');
$indexRequest->source([
    'name' => 'Smartphone',
    'price' => 699.99
]);
$bulkRequest->add($indexRequest);

// Add another index operation
$indexRequest2 = new IndexRequest('products');
$indexRequest2->id('2');
$indexRequest2->source([
    'name' => 'Laptop',
    'price' => 1299.99
]);
$bulkRequest->add($indexRequest2);

// Add an update operation
$updateRequest = new UpdateRequest('products');
$updateRequest->id('3');
$updateRequest->source([
    'price' => 499.99,
    'on_sale' => true
]);
$bulkRequest->add($updateRequest);

// Add a delete operation
$deleteRequest = new DeleteRequest('products');
$deleteRequest->id('4');
$bulkRequest->add($deleteRequest);

// Execute the bulk request
$response = $client->bulk($bulkRequest);
```

## Processing Bulk Response

The bulk response contains the results of each operation:

```php
<?php
// Check overall status
if (isset($response['errors']) && $response['errors'] === true) {
    echo "Some operations failed!\n";
} else {
    echo "All operations succeeded!\n";
}

// Process individual operation results
foreach ($response['items'] as $item) {
    // Each item contains one operation result
    $operationType = key($item); // 'index', 'update', or 'delete'
    $result = $item[$operationType];
    
    echo "Operation: {$operationType}, ID: {$result['_id']}, ";
    
    if (isset($result['error'])) {
        echo "Failed: {$result['error']['type']} - {$result['error']['reason']}\n";
    } else {
        echo "Success: {$result['result']}\n";
    }
}
```

## Bulk Indexing Example

Here's an example of bulk indexing a collection of products:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\IndexRequest;

$client = Client::getInstance(['localhost:9200']);

$products = [
    [
        'id' => '1',
        'name' => 'Smartphone',
        'price' => 699.99,
        'category' => 'electronics'
    ],
    [
        'id' => '2',
        'name' => 'Laptop',
        'price' => 1299.99,
        'category' => 'electronics'
    ],
    [
        'id' => '3',
        'name' => 'Headphones',
        'price' => 199.99,
        'category' => 'accessories'
    ],
    [
        'id' => '4',
        'name' => 'Smartwatch',
        'price' => 249.99,
        'category' => 'wearables'
    ]
];

$bulkRequest = new BulkRequest();

foreach ($products as $product) {
    $id = $product['id'];
    unset($product['id']); // Remove ID from source
    
    $indexRequest = new IndexRequest('products');
    $indexRequest->id($id);
    $indexRequest->source($product);
    
    $bulkRequest->add($indexRequest);
}

$response = $client->bulk($bulkRequest);

// Count successful operations
$successful = 0;
foreach ($response['items'] as $item) {
    $operation = key($item);
    if (!isset($item[$operation]['error'])) {
        $successful++;
    }
}

echo "Successfully indexed $successful out of " . count($products) . " products.";
```

## Bulk Updates Example

Here's an example of bulk updating multiple products:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;

$client = Client::getInstance(['localhost:9200']);

$updates = [
    '1' => ['on_sale' => true, 'discount' => 10],
    '2' => ['on_sale' => true, 'discount' => 15],
    '3' => ['on_sale' => true, 'discount' => 20]
];

$bulkRequest = new BulkRequest();

foreach ($updates as $id => $fields) {
    $updateRequest = new UpdateRequest('products');
    $updateRequest->id($id);
    $updateRequest->source($fields);
    
    $bulkRequest->add($updateRequest);
}

$response = $client->bulk($bulkRequest);
```

## Bulk Deletes Example

Here's an example of bulk deleting products:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\BulkRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);

$productIdsToDelete = ['101', '102', '103', '104'];

$bulkRequest = new BulkRequest();

foreach ($productIdsToDelete as $id) {
    $deleteRequest = new DeleteRequest('products');
    $deleteRequest->id($id);
    
    $bulkRequest->add($deleteRequest);
}

$response = $client->bulk($bulkRequest);
```

## Best Practices for Bulk Operations

1. **Batch Size**: Keep bulk requests to a reasonable size (typically 1,000-5,000 documents or 5-15MB).
2. **Error Handling**: Always check the response for errors, as some operations may fail while others succeed.
3. **Retries**: Implement retry logic for failed operations.
4. **Performance**: Use bulk operations instead of individual requests when processing multiple documents.
