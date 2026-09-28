# Aliases and Reindexing Without Downtime

Point your application at an **alias**, keep the data in versioned indices behind it, and switch the alias
atomically when a new version is ready. Readers never see an empty or half-filled index.

## Create a versioned index with its mapping

```php
<?php
use Zvonchuk\Elastic\Indices\CreateRequest;

$client->indices()->create((new CreateRequest('products_v2'))
    ->settings(['number_of_shards' => 1])
    ->mappings(['dynamic' => 'strict', 'properties' => ['name' => ['type' => 'text']]]));
```

`alias()` adds aliases at creation time: `->alias('products')` or `->alias('products_write', ['is_write_index' => true])`.

## Fill it, then swap

```php
<?php
// ... bulk-index into products_v2, check the document count ...

$previous = $client->indices()->swapAlias('products', 'products_v2');
// one atomic call: products → products_v2 only; $previous lists the indices it was taken from

foreach ($previous as $old) {
    // delete when you are sure the new index is good (or keep the last one for rollback)
}
```

## Lower-level operations

```php
<?php
use Zvonchuk\Elastic\Indices\UpdateAliasesRequest;

$client->indices()->updateAliases((new UpdateAliasesRequest())
    ->add('products_v3', 'products')
    ->remove('products_v2', 'products'));   // applied atomically

$client->indices()->existsAlias('products');      // bool
$client->indices()->indicesForAlias('products');  // list<string>
```
