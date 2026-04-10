<?php

declare(strict_types=1);

namespace Searoute;

final class Utils
{
    private const AVG_EARTH_RADIUS_KM = 6371008.8;

    private const CONVERSIONS = [
        'km' => 0.001,
        'm' => 1.0,
        'mi' => 0.000621371192,
        'ft' => 3.28084,
        'in' => 39.370,
        'deg' => 1 / 111325,
        'cen' => 100,
        'rad' => 1 / self::AVG_EARTH_RADIUS_KM,
        'naut' => 0.000539956803,
        'yd' => 0.914411119,
        'nm' => 0.00071506154,
    ];

    public static function fromNodesEdgesSet(Graph $graph, array $nodes, ?array $edges): Graph
    {
        return $graph->setData($nodes, $edges);
    }

    public static function validateLonLat(array $coord): void
    {
        if (count($coord) !== 2 || !is_numeric($coord[0]) || !is_numeric($coord[1])) {
            throw new \InvalidArgumentException('Invalid input format. Expected [lon, lat].');
        }

        if ($coord[1] < -90 || $coord[1] > 90) {
            throw new \InvalidArgumentException('Invalid longitude and/or latitude.');
        }
    }

    public static function distance(array $a, array $b, string $units = 'km'): float
    {
        $dLat = deg2rad($b[1] - $a[1]);
        $dLon = deg2rad($b[0] - $a[0]);
        $lat1 = deg2rad($a[1]);
        $lat2 = deg2rad($b[1]);

        $haversine = (sin($dLat / 2) ** 2) + (sin($dLon / 2) ** 2) * cos($lat1) * cos($lat2);
        $centralAngle = 2 * atan2(sqrt($haversine), sqrt(1 - $haversine));

        return $centralAngle * self::AVG_EARTH_RADIUS_KM * (self::CONVERSIONS[$units] ?? 1.0);
    }

    public static function distanceLength(array $line, string $units = 'km'): float
    {
        $length = 0.0;
        for ($i = 0, $max = count($line) - 1; $i < $max; $i++) {
            $length += self::distance($line[$i], $line[$i + 1], $units);
        }

        return $length;
    }

    public static function getDuration(float $speedKnot, float $length, string $units): float
    {
        if ($speedKnot <= 0) {
            return 0.0;
        }

        return $length / ($speedKnot * self::speedCoefficient($units));
    }

    public static function normalizeLineString(?array $previous, array $current): array
    {
        if ($previous === null) {
            return $current;
        }

        [$nowX, $nowY] = $current;
        [$prevX] = $previous;
        $delta = $prevX - $nowX;

        if ($delta < -180) {
            $nowX -= 360;
        } elseif ($delta > 180) {
            $nowX += 360;
        }

        return [$nowX, $nowY];
    }

    public static function loadGeoJsonIntoGraph(Graph $graph, array $payload): void
    {
        $features = ($payload['type'] ?? null) === 'FeatureCollection' ? ($payload['features'] ?? []) : [$payload];

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? [];
            $properties = $feature['properties'] ?? [];
            self::handleGeometry($graph, $geometry, $properties);
        }
    }

    private static function handleGeometry(Graph $graph, array $geometry, array $properties): void
    {
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Point') {
            $graph->addNode($coordinates, $properties);
            return;
        }

        if ($type === 'MultiPoint') {
            foreach ($coordinates as $point) {
                $graph->addNode($point, $properties);
            }
            return;
        }

        if ($type === 'LineString') {
            self::addLineEdges($graph, $coordinates, $properties);
            return;
        }

        if ($type === 'MultiLineString') {
            foreach ($coordinates as $line) {
                self::addLineEdges($graph, $line, $properties);
            }
        }
    }

    private static function addLineEdges(Graph $graph, array $coordinates, array $properties): void
    {
        for ($i = 0, $max = count($coordinates) - 1; $i < $max; $i++) {
            $graph->addEdge($coordinates[$i], $coordinates[$i + 1], $properties);
            $graph->addEdge($coordinates[$i + 1], $coordinates[$i], $properties);
        }
    }

    private static function speedCoefficient(string $unit): float
    {
        return match ($unit) {
            'km' => 1.852,
            'm' => 1852.0,
            'mi' => 1.15078,
            'ft' => 6076.12,
            'in' => 72913.4,
            'deg' => 1.852,
            'cen' => 185200.0,
            'rad' => 1.852,
            'yd' => 2025.37,
            default => 1.0,
        };
    }
}
