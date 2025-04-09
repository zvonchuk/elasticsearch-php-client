# Index Management

Various operations for managing Elasticsearch indices beyond creation and mapping.

## Checking If an Index Exists

To check if an index exists:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$indexRequest = new IndexRequest('products');
$exists = $indices->exists($indexRequest);

if ($exists) {
    echo "Index exists!";
} else {
    echo "Index does not exist.";
}
```

## Deleting an Index

To delete an index:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$deleteRequest = new DeleteRequest('products');
$response = $indices->delete($deleteRequest);

if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
    echo "Index deleted successfully!\n";
} else {
    echo "Failed to delete index.\n";
}
```

## Refreshing an Index

Refreshing makes all operations performed on an index since the last refresh available for search:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\RefreshRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$refreshRequest = new RefreshRequest('products');
$response = $indices->refresh($refreshRequest);

if (isset($response['_shards']['successful']) && $response['_shards']['successful'] > 0) {
    echo "Index refreshed successfully!\n";
} else {
    echo "Failed to refresh index.\n";
}
```

## Safe Index Deletion

Here's a pattern to safely check and delete an index:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\DeleteRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$indexName = 'products';

// Check if index exists first
$indexRequest = new IndexRequest($indexName);
$exists = $indices->exists($indexRequest);

if ($exists) {
    // Proceed with deletion
    $deleteRequest = new DeleteRequest($indexName);
    $response = $indices->delete($deleteRequest);
    
    if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
        echo "Index '$indexName' deleted successfully!\n";
    } else {
        echo "Failed to delete index '$indexName'.\n";
    }
} else {
    echo "Index '$indexName' does not exist, nothing to delete.\n";
}
```

## Example: Recreating an Index

Sometimes you need to delete and recreate an index. Here's how:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\DeleteRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;
use Zvonchuk\Elastic\Indices\PutMappingsRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$indexName = 'products';

// 1. Check if index exists
$indexRequest = new IndexRequest($indexName);
$exists = $indices->exists($indexRequest);

// 2. Delete the index if it exists
if ($exists) {
    $deleteRequest = new DeleteRequest($indexName);
    $deleteResponse = $indices->delete($deleteRequest);
    
    if (isset($deleteResponse['acknowledged']) && $deleteResponse['acknowledged'] === true) {
        echo "Existing index '$indexName' deleted.\n";
    } else {
        echo "Failed to delete existing index '$indexName'.\n";
        exit(1);  // Exit if deletion fails
    }
}

// 3. Create the index with new settings
$createRequest = new CreateRequest($indexName);
$createRequest->settings([
    'number_of_shards' => 2,
    'number_of_replicas' => 1
]);

$createResponse = $indices->create($createRequest);

if (isset($createResponse['acknowledged']) && $createResponse['acknowledged'] === true) {
    echo "Index '$indexName' created with new settings.\n";
    
    // 4. Add mappings
    $mappingsRequest = new PutMappingsRequest($indexName);
    $mappingsRequest->properties([
        'name' => [
            'type' => 'text'
        ],
        'price' => [
            'type' => 'float'
        ]
    ]);
    
    $mappingResponse = $indices->putMapping($mappingsRequest);
    
    if (isset($mappingResponse['acknowledged']) && $mappingResponse['acknowledged'] === true) {
        echo "Mappings added to index '$indexName'.\n";
    } else {
        echo "Failed to add mappings to index '$indexName'.\n";
    }
} else {
    echo "Failed to create index '$indexName'.\n";
}
```

## Example: Managing Multiple Indices

Here's how to manage multiple indices:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\DeleteRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

// List of indices to manage
$indexNames = ['products', 'customers', 'orders'];

// Check existence of each index
$existingIndices = [];
$missingIndices = [];

foreach ($indexNames as $indexName) {
    $indexRequest = new IndexRequest($indexName);
    if ($indices->exists($indexRequest)) {
        $existingIndices[] = $indexName;
    } else {
        $missingIndices[] = $indexName;
    }
}

echo "Existing indices: " . implode(', ', $existingIndices) . "\n";
echo "Missing indices: " . implode(', ', $missingIndices) . "\n";

// Create missing indices
foreach ($missingIndices as $indexName) {
    $createRequest = new CreateRequest($indexName);
    $createRequest->settings([
        'number_of_shards' => 2,
        'number_of_replicas' => 1
    ]);
    
    $response = $indices->create($createRequest);
    
    if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
        echo "Created index: $indexName\n";
    } else {
        echo "Failed to create index: $indexName\n";
    }
}

// Refresh all indices
foreach ($indexNames as $indexName) {
    $refreshRequest = new RefreshRequest($indexName);
    $indices->refresh($refreshRequest);
    echo "Refreshed index: $indexName\n";
}
```
