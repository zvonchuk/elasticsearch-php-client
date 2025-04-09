# Indices Management

Elasticsearch indices are collections of documents. This section covers operations for managing indices in Elasticsearch.

## Available Indices Operations

- [Creating Indices](create.html) - Creating new indices with settings
- [Mappings](mapping.html) - Defining field types and properties
- [Index Management](management.html) - Other operations like delete, refresh, and exists

## Indices API Structure

All indices operations in the elasticsearch-php-client are accessed through the indices() method:

```php
<?php
use Zvonchuk\Elastic\Client;

$client = Client::getInstance(['localhost:9200']);

// Access the indices API
$indices = $client->indices();

// Now you can perform various indices operations
// e.g., $indices->create(), $indices->exists(), etc.
```

## Basic Indices Workflow

A typical workflow for managing indices includes:

1. Checking if an index exists
2. Creating an index with settings if needed
3. Defining mappings for the fields
4. Refreshing the index when necessary

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;
use Zvonchuk\Elastic\Indices\PutMappingsRequest;
use Zvonchuk\Elastic\Indices\RefreshRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

// Step 1: Check if index exists
$indexRequest = new IndexRequest('my_index');
$exists = $indices->exists($indexRequest);

// Step 2: Create index if it doesn't exist
if (!$exists) {
    $createRequest = new CreateRequest('my_index');
    $createRequest->settings([
        'number_of_shards' => 3,
        'number_of_replicas' => 1
    ]);
    $indices->create($createRequest);
    
    // Step 3: Define mappings
    $mappingsRequest = new PutMappingsRequest('my_index');
    $mappingsRequest->properties([
        'title' => [
            'type' => 'text',
            'analyzer' => 'standard'
        ],
        'created_at' => [
            'type' => 'date'
        ],
        'price' => [
            'type' => 'float'
        ]
    ]);
    $indices->putMapping($mappingsRequest);
    
    echo "Index created with mappings.\n";
} else {
    echo "Index already exists.\n";
}

// Step 4: Refresh the index (when needed)
$refreshRequest = new RefreshRequest('my_index');
$indices->refresh($refreshRequest);
```

Browse the sections to learn more about each indices operation.
