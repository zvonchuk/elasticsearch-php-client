# Deleting Documents

Removing documents from Elasticsearch.

## Basic Document Deletion

To delete a document by its ID:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new DeleteRequest('products');
$request->id('1');

$response = $client->delete($request);
```

The response contains information about the deletion:

```
[
  "_index" => "products",
  "_id" => "1",
  "_version" => 2,
  "result" => "deleted",
  "_shards" => [
    "total" => 2,
    "successful" => 1,
    "failed" => 0
  ],
  "_seq_no" => 2,
  "_primary_term" => 1
]
```

## Checking Deletion Results

You can check the response to see if the deletion was successful:

```php
<?php
if (isset($response['result']) && $response['result'] === 'deleted') {
    echo "Document {$response['_id']} was successfully deleted.\n";
} elseif (isset($response['result']) && $response['result'] === 'not_found') {
    echo "Document {$response['_id']} was not found.\n";
} else {
    echo "Error deleting document.\n";
}
```

## Handling Non-Existent Documents

When trying to delete a document that doesn't exist, Elasticsearch returns a "not_found" result:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new DeleteRequest('products');
$request->id('non_existent_id');

$response = $client->delete($request);

if (isset($response['result']) && $response['result'] === 'not_found') {
    echo "Document not found, nothing to delete.\n";
}
```

## Example: Delete with Verification

Here's a pattern to verify before deleting:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\ExistsRequest;
use Zvonchuk\Elastic\Core\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);
$productId = '1';

// 1. Check if the document exists
$existsRequest = new ExistsRequest('products');
$existsRequest->id($productId);
$exists = $client->exists($existsRequest);

if (!$exists) {
    echo "Product $productId does not exist, nothing to delete.\n";
} else {
    // 2. Delete the document
    $deleteRequest = new DeleteRequest('products');
    $deleteRequest->id($productId);
    $response = $client->delete($deleteRequest);
    
    if (isset($response['result']) && $response['result'] === 'deleted') {
        echo "Product $productId was successfully deleted.\n";
    } else {
        echo "Error deleting product $productId.\n";
    }
}
```

## Example: Batch Deletion

Here's how to delete multiple documents in a loop:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);

$productIds = ['1', '2', '3'];
$deletedCount = 0;

foreach ($productIds as $id) {
    $request = new DeleteRequest('products');
    $request->id($id);
    
    try {
        $response = $client->delete($request);
        if (isset($response['result']) && $response['result'] === 'deleted') {
            $deletedCount++;
        }
    } catch (\Exception $e) {
        echo "Error deleting product $id: " . $e->getMessage() . "\n";
    }
}

echo "Deleted $deletedCount out of " . count($productIds) . " products.";
```

For deleting many documents at once, consider using the [Bulk API](bulk.html) or the [Delete By Query API](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete-by-query.html).
