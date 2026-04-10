<?php

declare(strict_types=1);

use Searoute\Marnet;
use Searoute\Passage;
use Searoute\Searoute;
use function Searoute\from_nodes_edges_set;
use function Searoute\searoute;

it('migrates the manual python route example to pest', function (): void {
    $origin = [21.1545, 55.6526];
    $destination = [-118.2629, 33.7276];

    $route = searoute(
        $origin,
        $destination,
        appendOrigDest: true,
        restrictions: [Passage::NORTHWEST],
        returnPassages: true
    );

    expect($route['type'])->toBe('Feature')
        ->and($route['geometry']['type'])->toBe('LineString')
        ->and($route['properties']['units'])->toBe('km')
        ->and($route['properties']['length'])->toBeFloat()->toBeGreaterThan(15000)
        ->and($route['properties']['length'])->toBeLessThan(17000)
        ->and($route['properties']['traversed_passages'])->toBe(['panama'])
        ->and($route['geometry']['coordinates'][0])->toBe($origin)
        ->and($route['geometry']['coordinates'][array_key_last($route['geometry']['coordinates'])])->toBe($destination);
});

it('supports alternative output units', function (): void {
    $origin = [21.1545, 55.6526];
    $destination = [-118.2629, 33.7276];

    $routeKilometers = searoute($origin, $destination, units: 'km');
    $routeMiles = searoute($origin, $destination, units: 'mi');

    expect($routeMiles['properties']['units'])->toBe('mi')
        ->and($routeMiles['properties']['length'])->toBeFloat()->toBeGreaterThan(0.0)
        ->and($routeMiles['properties']['length'])
            ->toBeGreaterThan($routeKilometers['properties']['length'] * 0.60)
        ->and($routeMiles['properties']['length'])
            ->toBeLessThan($routeKilometers['properties']['length'] * 0.65);
});

it('rejects invalid coordinates', function (): void {
    searoute([10, 95], [20, 30]);
})->throws(InvalidArgumentException::class);

it('routes through a custom network built from nodes and edges', function (): void {
    $nodes = [
        '0,0' => ['x' => 0.0, 'y' => 0.0],
        '1,0' => ['x' => 1.0, 'y' => 0.0],
        '2,0' => ['x' => 2.0, 'y' => 0.0],
    ];

    $edges = [
        '0,0' => [
            '1,0' => ['weight' => 10.0],
        ],
        '1,0' => [
            '0,0' => ['weight' => 10.0],
            '2,0' => ['weight' => 20.0],
        ],
        '2,0' => [
            '1,0' => ['weight' => 20.0],
        ],
    ];

    $marnet = from_nodes_edges_set(new Marnet(), $nodes, $edges);
    $route = Searoute::searoute([0, 0], [2, 0], marnet: $marnet);

    expect($route['geometry']['coordinates'])->toBe([[0.0, 0.0], [1.0, 0.0], [2.0, 0.0]])
        ->and($route['properties']['length'])->toBeGreaterThan(0.0);
});
