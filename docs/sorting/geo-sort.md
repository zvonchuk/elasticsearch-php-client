# Geo Distance Sort

Geo distance sorting allows you to sort documents based on their distance from a specified geographical point.

## Basic Geo Distance Sort

To sort by distance from a specific location:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

// Sort by distance from New York City
$sort = SortBuilders::geoDistanceSort('location', 40.7128, -74.0060);
```

## Specifying Sort Order

By default, results are sorted by ascending distance (closest first), but you can change this:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

// Closest first (default)
$closestFirstSort = SortBuilders::geoDistanceSort('location', 40.7128, -74.0060)
    ->order(SortBuilder::ASC);

// Farthest first
$farthestFirstSort = SortBuilders::geoDistanceSort('location', 40.7128, -74.0060)
    ->order(SortBuilder::DESC);
```

## Specifying Distance Unit

You can specify the unit in which distances are calculated:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\GeoSort;

// Sort by distance in kilometers
$sortKm = SortBuilders::geoDistanceSort('location', 40.7128, -74.0060)
    ->unit(GeoSort::KILOMETERS);

// Sort by distance in miles
$sortMiles = SortBuilders::geoDistanceSort('location', 40.7128, -74.0060)
    ->unit(GeoSort::MILES);
```

Available units include:
- `GeoSort::METERS` (default)
- `GeoSort::KILOMETERS`
- `GeoSort::MILES`
- `GeoSort::NAUTICALMILES`
- `GeoSort::YARDS`
- `GeoSort::FEET`
- `GeoSort::INCHES`
- `GeoSort::CENTIMETERS`
- `GeoSort::MILLIMETERS`

## Example: Finding Nearby Restaurants

Here's a complete example of finding restaurants sorted by distance:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\GeoSort;

$client = Client::getInstance(['localhost:9200']);

// User's location (New York City)
$userLat = 40.7128;
$userLon = -74.0060;

// Create a search for restaurants
$searchSource = new SearchSourceBuilder();
$searchSource->query(
    QueryBuilders::boolQuery()
        ->must(QueryBuilders::termQuery('type', 'restaurant'))
        ->filter(
            QueryBuilders::GeoDistanceQuery('location')
                ->point($userLat, $userLon)
                ->distance('5km')
        )
);

// Sort by distance from user's location
$searchSource->sort(
    SortBuilders::geoDistanceSort('location', $userLat, $userLon)
        ->unit(GeoSort::KILOMETERS)
);

// Add rating as a secondary sort
$searchSource->sort(
    SortBuilders::fieldSort('rating')->order('desc')
);

// Execute the search
$request = new SearchRequest('places');
$request->source($searchSource);
$response = $client->search($request);

// Process results
echo "Restaurants within 5km of your location, sorted by distance:\n";
foreach ($response->getHits() as $hit) {
    $restaurant = $hit['_source'];
    $distance = isset($hit['sort'][0]) ? round($hit['sort'][0], 2) . 'km' : 'unknown';
    
    echo "- {$restaurant['name']}\n";
    echo "  Distance: $distance\n";
    echo "  Rating: {$restaurant['rating']}/5\n";
    echo "  Address: {$restaurant['address']}\n\n";
}
```

## Combining Geo Distance Sort with Other Sorts

You can combine geo distance sorting with other sort types:

```php
<?php
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\GeoSort;

$searchSource = new SearchSourceBuilder();

// First sort by premium status
$searchSource->sort(
    SortBuilders::fieldSort('is_premium')->order('desc')
);

// Then sort by distance
$searchSource->sort(
    SortBuilders::geoDistanceSort('location', 40.7128, -74.0060)
        ->unit(GeoSort::KILOMETERS)
);

// Then by rating
$searchSource->sort(
    SortBuilders::fieldSort('rating')->order('desc')
);
```

## Example: Find and Group Locations by Distance

Here's an example that finds locations and groups them by distance ranges:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\GeoSort;

$client = Client::getInstance(['localhost:9200']);

// User's location
$userLat = 40.7128;
$userLon = -74.0060;

// Create a search for places within 10km
$searchSource = new SearchSourceBuilder();
$searchSource->query(
    QueryBuilders::GeoDistanceQuery('location')
        ->point($userLat, $userLon)
        ->distance('10km')
);

// Sort by distance
$searchSource->sort(
    SortBuilders::geoDistanceSort('location', $userLat, $userLon)
        ->unit(GeoSort::KILOMETERS)
);

// Execute the search
$request = new SearchRequest('places');
$request->source($searchSource);
$response = $client->search($request);

// Group results by distance
$nearby = []; // 0-1km
$walkable = []; // 1-3km
$drivable = []; // 3-10km

foreach ($response->getHits() as $hit) {
    $place = $hit['_source'];
    $distance = $hit['sort'][0]; // Distance in km
    
    if ($distance <= 1) {
        $nearby[] = [
            'name' => $place['name'],
            'distance' => round($distance, 2) . 'km'
        ];
    } elseif ($distance <= 3) {
        $walkable[] = [
            'name' => $place['name'],
            'distance' => round($distance, 2) . 'km'
        ];
    } else {
        $drivable[] = [
            'name' => $place['name'],
            'distance' => round($distance, 2) . 'km'
        ];
    }
}

// Display grouped results
echo "Nearby Places (0-1km):\n";
foreach ($nearby as $place) {
    echo "- {$place['name']} ({$place['distance']})\n";
}

echo "\nWalkable Places (1-3km):\n";
foreach ($walkable as $place) {
    echo "- {$place['name']} ({$place['distance']})\n";
}

echo "\nDrivable Places (3-10km):\n";
foreach ($drivable as $place) {
    echo "- {$place['name']} ({$place['distance']})\n";
}
```
