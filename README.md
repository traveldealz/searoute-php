# searoute-php

Standalone PHP port of `searoute` for generating realistic maritime routes from the bundled sea network.

## Installation

```bash
composer require traveldealz/searoute-php
```

## Requirements

- PHP 8.3+

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use function Searoute\searoute;

$route = searoute(
    [0.3515625, 50.064191736659104],
    [117.42187500000001, 39.36827914916014],
    returnPassages: true
);

echo $route['properties']['length'] . ' ' . $route['properties']['units'] . PHP_EOL;
print_r($route['properties']['traversed_passages']);
```

## API

- `Searoute\searoute(...)`
- `Searoute\setup_marnet(?string $path = null)`
- `Searoute\from_nodes_edges_set(Graph $graph, array $nodes, ?array $edges)`

## Development

```bash
composer install
composer test
composer run test:smoke
composer run example
```

## Data

The package uses the bundled GeoJSON network in `data/marnet_searoute.geojson`.

## Disclaimer

This library is intended for visualization and analysis, not for real-world nautical navigation.
