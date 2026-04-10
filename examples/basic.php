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

echo number_format((float) $route['properties']['length'], 1) . ' ' . $route['properties']['units'] . PHP_EOL;
echo json_encode($route, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
