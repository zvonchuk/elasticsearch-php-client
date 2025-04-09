# Updating Documents

Updating existing documents in Elasticsearch.

## Basic Document Update

To update a document by its ID:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\UpdateRequest;

$client = Client::getInstance(['localhost:9200']);

$request = new UpdateRequest('products');
$request->id('1');
$request->source([
    'price' => 649.99,
    'on_sale' => true,
    'updated_at' => date('c')  // ISO 8601 date
]);

$response = $client->update($request);
```

The response contains information about the update:

```
[
  "_index" => "products",
  "_id" => "1",
  "_version" => 2,
  "result" => "updated",
  "_shards" => [
    "total" => 2,
    "successful" => 1,
    "failed" => 0
  ],
  "_seq_no" => 1,
  "_primary_term" => 1
]
```

## Partial Updates

The update operation only changes the fields you specify. Other fields remain unchanged:

```php
<?php
use Zvonchuk\Elastic\Core\UpdateRequest;

$request = new UpdateRequest('products');
$request->id('1');
$request->source([
    'in_stock' => false,  // Only update this field
    'updated_at' => date('c')
]);

$response = $client->update($request);
```

## Updating Nested Fields

You can update nested fields in a document:

```php
<?php
use Zvonchuk\Elastic\Core\UpdateRequest;

$request = new UpdateRequest('products');
$request->id('2');
$request->source([
    'specifications.storage' => '1TB SSD',  // Update a specific nested field
    'specifications.ram' => '32GB'
]);

$response = $client->update($request);
```

## Checking Update Results

You can check the response to see if the update was successful:

```php
<?php
if ($response['result'] === 'updated') {
    echo "Document {$response['_id']} was successfully updated.\n";
    echo "New version: {$response['_version']}\n";
} else {
    echo "Error updating document.\n";
}
```

## Update and Retry Pattern

If you need to ensure updates happen even with concurrent modifications, you can implement a retry pattern:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\GetRequest;
use Zvonchuk\Elastic\Core\UpdateRequest;

$client = Client::getInstance(['localhost:9200']);
$productId = '1';
$maxRetries = 3;
$retries = 0;

while ($retries < $maxRetries) {
    try {
        // 1. Get the current document
        $getRequest = new GetRequest('products');
        $getRequest->id($productId);
        $document = $client->get($getRequest);
        
        if (!isset($document['found']) || $document['found'] !== true) {
            echo "Document not found!\n";
            break;
        }
        
        // 2. Prepare the update with the current document version
        $currentStock = $document['_source']['stock'] ?? 0;
        $newStock = $currentStock - 1;
        
        if ($newStock < 0) {
            echo "Cannot reduce stock below 0!\n";
            break;
        }
        
        // 3. Update the document
        $updateRequest = new UpdateRequest('products');
        $updateRequest->id($productId);
        $updateRequest->source([
            'stock' => $newStock,
            'updated_at' => date('c')
        ]);
        
        $response = $client->update($updateRequest);
        echo "Stock updated successfully to $newStock\n";
        break;
    } catch (\Exception $e) {
        // Handle version conflicts or other errors
        $retries++;
        if ($retries >= $maxRetries) {
            echo "Failed to update after $maxRetries attempts\n";
        } else {
            echo "Retry attempt $retries...\n";
            usleep(100000);  // 100ms delay before retry
        }
    }
}
```

## Example: Batch Updates

Here's how to update multiple documents with a common field:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\UpdateRequest;

$client = Client::getInstance(['localhost:9200']);

$productIds = ['1', '2', '3'];
$updatedCount = 0;

foreach ($productIds as $id) {
    $request = new UpdateRequest('products');
    $request->id($id);
    $request->source([
        'sale_ends' => '2023-12-31',
        'discount' => 15
    ]);
    
    try {
        $response = $client->update($request);
        if ($response['result'] === 'updated') {
            $updatedCount++;
        }
    } catch (\Exception $e) {
        echo "Error updating product $id: " . $e->getMessage() . "\n";
    }
}

echo "Updated $updatedCount out of " . count($productIds) . " products.";
```

For updating many documents at once, consider using the [Bulk API](bulk.html) or the [Update By Query API](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update-by-query.html).
