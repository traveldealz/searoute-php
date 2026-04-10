<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/autoload.php';

use function Searoute\searoute;

$route = searoute(
    [21.1545, 55.6526],
    [-118.2629, 33.7276],
    appendOrigDest: true,
    returnPassages: true
);

if (($route['type'] ?? null) !== 'Feature') {
    fwrite(STDERR, "Expected GeoJSON Feature.\n");
    exit(1);
}

if (($route['properties']['traversed_passages'] ?? []) !== ['panama']) {
    fwrite(STDERR, "Unexpected traversed passages.\n");
    exit(1);
}

echo "Smoke test passed.\n";
