# Geo Queries

Geo queries allow you to search for documents based on geographic locations. The elasticsearch-php-client supports various geo queries for different use cases.

## Geo Distance Query

Finds documents within a certain distance from a point:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::GeoDistanceQuery('location')
    ->point(40.7128, -74.0060)  // latitude, longitude
    ->distance('10km');
```

This generates:

```json
{
  "geo_distance": {
    "distance": "10km",
    "location": "40.7128,-74.0060"
  }
}
```

## Geo Bounding Box Query

Finds documents with geo-points within a bounding box:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::geoBoundingBoxQuery('location')
    ->topLeft(42.0, -72.0)
    ->bottomRight(40.0, -74.0);
```

Alternatively, you can use the bounding method:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::geoBoundingBoxQuery('location')
    ->bounding([
        'top_left' => [42.0, -72.0],
        'bottom_right' => [40.0, -74.0]
    ]);
```

This generates:

```json
{
  "geo_bounding_box": {
    "location": {
      "top_left": [42.0, -72.0],
      "bottom_right": [40.0, -74.0]
    }
  }
}
```

## Combining Geo Queries with Other Query Types

Geo queries can be combined with other query types using a bool query:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('category', 'restaurant'))
    ->filter(
        QueryBuilders::GeoDistanceQuery('location')
            ->point(40.7128, -74.0060)
            ->distance('5km')
    );
```

## Example: Finding Nearby Restaurants

Here's a complete example of using geo distance to find nearby restaurants:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

// Create a query for restaurants within 5km
$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('type', 'restaurant'))
    ->filter(
        QueryBuilders::GeoDistanceQuery('location')
            ->point(40.7128, -74.0060)  // New York City coordinates
            ->distance('5km')
    );

// Set up the search with sorting by distance
$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);
$searchSource->sort(
    SortBuilders::geoDistanceSort('location', 40.7128, -74.0060)
        ->order('asc')  // closest first
        ->unit('km')
);

// Execute the search
$request = new SearchRequest('places');
$request->source($searchSource);
$response = $client->search($request);

// Process results
foreach ($response->getHits() as $hit) {
    $name = $hit['_source']['name'];
    $distance = isset($hit['sort'][0]) ? round($hit['sort'][0], 2) . 'km' : 'unknown';
    
    echo "Restaurant: {$name}, Distance: {$distance}\n";
}
```
