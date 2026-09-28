# Geo Queries

Geo queries allow you to search for documents based on geographic locations. The elasticsearch-php-client supports various geo queries for different use cases.

## Geo Distance Query

Finds documents within a certain distance from a point:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::geoDistanceQuery('location')
    ->point(40.7128, -74.0060)  // latitude, longitude
    ->distance('10km');
```

This generates (the point goes under the field you passed):

```json
{
  "geo_distance": {
    "distance": "10km",
    "location": { "lat": 40.7128, "lon": -74.006 }
  }
}
```

## Geo Bounding Box Query

Finds documents with geo-points within a bounding box:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$query = QueryBuilders::geoBoundingBoxQuery('location')
    ->topLeft(['lat' => 42.0, 'lon' => -74.0])
    ->bottomRight(['lat' => 40.0, 'lon' => -72.0]);
```

Each corner is one point: `['lat' => .., 'lon' => ..]`, a `"lat,lon"` string or a geohash. This generates:

```json
{
  "geo_bounding_box": {
    "location": {
      "top_left": { "lat": 42.0, "lon": -74.0 },
      "bottom_right": { "lat": 40.0, "lon": -72.0 }
    }
  }
}
```

> `bounding()` from 0.x is deprecated and will be removed in 2.0: it ignores corners set with the setters above,
> `boost()` and `queryName()`, and returns an array instead of a query. Use the corner setters.

## Combining Geo Queries with Other Query Types

Geo queries can be combined with other query types using a bool query:

```php
<?php
use Zvonchuk\Elastic\Query\QueryBuilders;

$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('category', 'restaurant'))
    ->filter(
        QueryBuilders::geoDistanceQuery('location')
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
        QueryBuilders::geoDistanceQuery('location')
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
