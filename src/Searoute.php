<?php

declare(strict_types=1);

namespace Searoute;

final class Searoute
{
    private static ?Marnet $defaultMarnet = null;

    public static function setupMarnet(?string $path = null): Marnet
    {
        if (self::$defaultMarnet === null) {
            $dataPath = $path ?? dirname(__DIR__) . '/data/marnet_searoute.geojson';
            self::$defaultMarnet = (new Marnet())->loadGeoJson($dataPath);
        }

        return clone self::$defaultMarnet;
    }

    public static function searoute(
        array $origin,
        array $destination,
        string $units = 'km',
        float $speedKnot = 24,
        bool $appendOrigDest = false,
        ?array $restrictions = [Passage::NORTHWEST],
        ?Marnet $marnet = null,
        bool $returnPassages = false
    ): array {
        Utils::validateLonLat($origin);
        Utils::validateLonLat($destination);

        $marnet ??= self::setupMarnet();

        if ($restrictions !== null) {
            $marnet->restrictions = $restrictions;
        }
        $path = $marnet->shortestPath($origin, $destination);

        if ($appendOrigDest) {
            if ($path !== [] && $path[0] !== $origin) {
                array_unshift($path, $origin);
            }
            if ($path !== [] && $path[array_key_last($path)] !== $destination) {
                $path[] = $destination;
            }
        }

        $normalized = [];
        $previous = null;
        foreach ($path as $point) {
            $fixed = Utils::normalizeLineString($previous, $point);
            $normalized[] = $fixed;
            $previous = $fixed;
        }

        $length = Utils::distanceLength($normalized, $units);
        $feature = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $normalized,
            ],
            'properties' => [
                'length' => $length,
                'units' => $units,
                'duration_hours' => Utils::getDuration($speedKnot, $length, $units),
            ],
        ];

        if ($returnPassages) {
            $feature['properties']['traversed_passages'] = Passage::filterValidPassages($marnet->traversedPassages);
        }

        return $feature;
    }

    public static function fromNodesEdgesSet(Graph $graph, array $nodes, ?array $edges): Graph
    {
        return Utils::fromNodesEdgesSet($graph, $nodes, $edges);
    }
}
