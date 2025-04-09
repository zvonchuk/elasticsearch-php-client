# Creating Indices

Creating new indices in Elasticsearch with custom settings.

## Basic Index Creation

To create a new index with default settings:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\CreateRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$createRequest = new CreateRequest('products');
$response = $indices->create($createRequest);

if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
    echo "Index created successfully!\n";
} else {
    echo "Failed to create index.\n";
}
```

## Creating an Index with Settings

You can specify various settings when creating an index:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\CreateRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$createRequest = new CreateRequest('products');
$createRequest->settings([
    'number_of_shards' => 3,
    'number_of_replicas' => 1,
    'refresh_interval' => '1s'
]);

$response = $indices->create($createRequest);
```

## Advanced Index Settings

Here's an example with more advanced settings including analyzers:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\CreateRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$createRequest = new CreateRequest('blog_posts');
$createRequest->settings([
    'number_of_shards' => 2,
    'number_of_replicas' => 1,
    'analysis' => [
        'analyzer' => [
            'my_custom_analyzer' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => ['lowercase', 'asciifolding', 'my_edge_ngram']
            ]
        ],
        'filter' => [
            'my_edge_ngram' => [
                'type' => 'edge_ngram',
                'min_gram' => 2,
                'max_gram' => 10
            ]
        ]
    ]
]);

$response = $indices->create($createRequest);
```

## Checking If Index Exists Before Creation

It's a good practice to check if an index exists before attempting to create it:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$indexName = 'products';

// Check if index exists
$indexRequest = new IndexRequest($indexName);
$exists = $indices->exists($indexRequest);

if (!$exists) {
    // Create index if it doesn't exist
    $createRequest = new CreateRequest($indexName);
    $createRequest->settings([
        'number_of_shards' => 3,
        'number_of_replicas' => 1
    ]);
    $response = $indices->create($createRequest);
    
    if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
        echo "Index '$indexName' created successfully!\n";
    } else {
        echo "Failed to create index '$indexName'.\n";
    }
} else {
    echo "Index '$indexName' already exists.\n";
}
```

## Example: Creating Multiple Indices

Here's how to create multiple indices with different settings:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Indices\IndexRequest;
use Zvonchuk\Elastic\Indices\CreateRequest;

$client = Client::getInstance(['localhost:9200']);
$indices = $client->indices();

$indexConfigs = [
    'products' => [
        'shards' => 3,
        'replicas' => 1
    ],
    'customers' => [
        'shards' => 2,
        'replicas' => 1
    ],
    'orders' => [
        'shards' => 4,
        'replicas' => 2
    ]
];

foreach ($indexConfigs as $indexName => $config) {
    // Check if index exists
    $indexRequest = new IndexRequest($indexName);
    $exists = $indices->exists($indexRequest);
    
    if (!$exists) {
        // Create index with specific settings
        $createRequest = new CreateRequest($indexName);
        $createRequest->settings([
            'number_of_shards' => $config['shards'],
            'number_of_replicas' => $config['replicas']
        ]);
        
        $response = $indices->create($createRequest);
        
        if (isset($response['acknowledged']) && $response['acknowledged'] === true) {
            echo "Index '$indexName' created with {$config['shards']} shards and {$config['replicas']} replicas.\n";
        } else {
            echo "Failed to create index '$indexName'.\n";
        }
    } else {
        echo "Index '$indexName' already exists, skipping.\n";
    }
}
```
