# Index Mappings

Mappings define how documents and their fields are stored and indexed in Elasticsearch.

## Putting Mappings on an Index

To define mappings for an index:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\PutMappingsRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$mappingsRequest = new PutMappingsRequest('products');
$mappingsRequest->properties([
    'name' => [
        'type' => 'text',
        'analyzer' => 'standard',
        'fields' => [
            'keyword' => [
                'type' => 'keyword',
                'ignore_above' => 256
            ]
        ]
    ],
    'description' => [
        'type' => 'text'
    ],
    'price' => [
        'type' => 'float'
    ],
    'created_at' => [
        'type' => 'date'
    ],
    'in_stock' => [
        'type' => 'boolean'
    ],
    'category' => [
        'type' => 'keyword'
    ]
]);

$response = $indices->putMapping($mappingsRequest);

if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
    echo "Mappings added successfully!\n";
} else {
    echo "Failed to add mappings.\n";
}
```

## Getting Mappings

To retrieve the mappings for an index:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\GetMappingsRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$getMappingsRequest = new GetMappingsRequest('products');
$mappings = $indices->getMapping($getMappingsRequest);

print_r($mappings);
```

## Common Field Types

Here are examples of common field types in Elasticsearch mappings:

```php
<?php
use Zvonchuk\Elastic\Indices\PutMappingsRequest;

$mappingsRequest = new PutMappingsRequest('my_index');
$mappingsRequest->properties([
    // Text fields for full-text search
    'title' => [
        'type' => 'text',
        'analyzer' => 'standard'
    ],
    
    // Keyword fields for exact matching and aggregations
    'tags' => [
        'type' => 'keyword'
    ],
    
    // Numeric types
    'price' => [
        'type' => 'float'
    ],
    'quantity' => [
        'type' => 'integer'
    ],
    'product_id' => [
        'type' => 'long'
    ],
    'rating' => [
        'type' => 'double'
    ],
    
    // Date fields
    'created_at' => [
        'type' => 'date',
        'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
    ],
    
    // Boolean fields
    'active' => [
        'type' => 'boolean'
    ],
    
    // Geo-point for location-based queries
    'location' => [
        'type' => 'geo_point'
    ],
    
    // Object for nested JSON objects
    'metadata' => [
        'type' => 'object',
        'properties' => [
            'author' => [
                'type' => 'keyword'
            ],
            'version' => [
                'type' => 'integer'
            ]
        ]
    ],
    
    // Nested type for arrays of objects that need to be queried independently
    'reviews' => [
        'type' => 'nested',
        'properties' => [
            'user_id' => [
                'type' => 'keyword'
            ],
            'rating' => [
                'type' => 'byte'
            ],
            'comment' => [
                'type' => 'text'
            ]
        ]
    ]
]);
```

## Multi-fields Mapping

You can define multiple ways to index the same field:

```php
<?php
use Zvonchuk\Elastic\Indices\PutMappingsRequest;

$mappingsRequest = new PutMappingsRequest('products');
$mappingsRequest->properties([
    'name' => [
        'type' => 'text',     // For full-text search
        'analyzer' => 'standard',
        'fields' => [
            'keyword' => [    // For exact matching and sorting
                'type' => 'keyword',
                'ignore_above' => 256
            ],
            'english' => [    // With English analyzer
                'type' => 'text',
                'analyzer' => 'english'
            ]
        ]
    ]
]);
```

## Example: Complete Index Setup with Mappings

Here's a complete example to create an index with mappings:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;
use Zvonchuk\Elastic\Indices\PutMappingsRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$indexName = 'products';

// Check if index exists
$indexRequest = new IndexRequest($indexName);
$exists = $indices->exists($indexRequest);

if (!$exists) {
    // 1. Create the index with settings
    $createRequest = new CreateRequest($indexName);
    $createRequest->settings([
        'number_of_shards' => 3,
        'number_of_replicas' => 1,
        'analysis' => [
            'analyzer' => [
                'product_analyzer' => [
                    'type' => 'custom',
                    'tokenizer' => 'standard',
                    'filter' => ['lowercase', 'asciifolding']
                ]
            ]
        ]
    ]);
    
    $response = $indices->create($createRequest);
    
    if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
        echo "Index created successfully.\n";
        
        // 2. Define mappings
        $mappingsRequest = new PutMappingsRequest($indexName);
        $mappingsRequest->properties([
            'name' => [
                'type' => 'text',
                'analyzer' => 'product_analyzer',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'description' => [
                'type' => 'text'
            ],
            'sku' => [
                'type' => 'keyword'
            ],
            'price' => [
                'type' => 'float'
            ],
            'category' => [
                'type' => 'keyword'
            ],
            'tags' => [
                'type' => 'keyword'
            ],
            'created_at' => [
                'type' => 'date'
            ],
            'in_stock' => [
                'type' => 'boolean'
            ],
            'rating' => [
                'type' => 'float'
            ],
            'location' => [
                'type' => 'geo_point'
            ]
        ]);
        
        $mappingResponse = $indices->putMapping($mappingsRequest);
        
        if (isset($mappingResponse['acknowledged']) && $mappingResponse['acknowledged'] === true) {
            echo "Mappings added successfully.\n";
        } else {
            echo "Failed to add mappings.\n";
        }
    } else {
        echo "Failed to create index.\n";
    }
} else {
    echo "Index already exists.\n";
}
```
