<?php

declare(strict_types=1);

namespace Searoute;

final class KDTree
{
    private array $points = [];

    public function __construct(array $points = [])
    {
        $this->points = array_values($points);
    }

    public function addPoint(array $point): void
    {
        $this->points[] = $point;
    }

    public function query(?array $point): array
    {
        if ($point === null) {
            throw new \RuntimeException('There are no nodes in the graph.');
        }

        if ($this->points === []) {
            throw new \RuntimeException('Ports/Marnet network was not initiated.');
        }

        $best = null;
        $bestDistance = INF;

        foreach ($this->points as $candidate) {
            $distance = $this->distance($point, $candidate);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $candidate;
            }
        }

        return $best;
    }

    private function distance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $index => $value) {
            $sum += (($value - $b[$index]) ** 2);
        }

        return sqrt($sum);
    }
}
