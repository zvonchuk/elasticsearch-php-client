# Document Operations

The elasticsearch-php-client provides a comprehensive set of operations for working with documents in Elasticsearch.

## Available Document Operations

- [Indexing Documents](index-doc.html) - Adding documents to an index
- [Getting Documents](get.html) - Retrieving documents by ID
- [Updating Documents](update.html) - Modifying existing documents
- [Deleting Documents](delete.html) - Removing documents
- [Bulk Operations](bulk.html) - Performing multiple operations in a single request

## Document Structure

In Elasticsearch, documents are JSON objects stored in an index. Each document has:

- An **index** name (where it's stored)
- A **unique ID** (either provided or auto-generated)
- A **source** (the actual JSON data)

All document operations in the elasticsearch-php-client follow a similar pattern:

```php
<?php
use Zvonchuk\Elastic\Core\IndexRequest;  // or other request types

$request = new IndexRequest('my_index');  // Specify the index
$request->id('document_id');              // Specify the document ID
$request->source([                        // Provide the document source
    'field1' => 'value1',
    'field2' => 'value2'
]);

$response = $client->index($request);     // Execute the operation
```

Browse the sections to learn more about each document operation.
