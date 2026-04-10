<?php

declare(strict_types=1);

namespace Searoute;

final class Marnet extends Graph
{
    public array $restrictions = [Passage::NORTHWEST];
    public array $traversedPassages = [];

    public function shortestPath(array $origin, array $destination): array
    {
        $originNode = $this->kdtree->query($origin);
        $destinationNode = $this->kdtree->query($destination);

        $originKey = Coordinate::key($originNode);
        $destinationKey = Coordinate::key($destinationNode);

        $distances = [$originKey => 0.0];
        $previous = [];
        $queue = new \SplPriorityQueue();
        $queue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
        $queue->insert($originKey, 0.0);

        while (!$queue->isEmpty()) {
            $current = $queue->extract();
            $currentKey = $current['data'];
            $currentDistance = -$current['priority'];

            if ($currentDistance > ($distances[$currentKey] ?? INF)) {
                continue;
            }

            if ($currentKey === $destinationKey) {
                break;
            }

            foreach ($this->adjacency[$currentKey] ?? [] as $neighborKey => $edgeData) {
                $passage = $edgeData['passage'] ?? null;
                if ($passage !== null && in_array($passage, $this->restrictions, true)) {
                    continue;
                }

                $weight = (float) ($edgeData['weight'] ?? 1.0);
                $candidateDistance = $currentDistance + $weight;

                if ($candidateDistance < ($distances[$neighborKey] ?? INF)) {
                    $distances[$neighborKey] = $candidateDistance;
                    $previous[$neighborKey] = [$currentKey, $passage];
                    $queue->insert($neighborKey, -$candidateDistance);
                }
            }
        }

        if (!isset($distances[$destinationKey])) {
            return [];
        }

        $keys = [];
        $this->traversedPassages = [];
        for ($key = $destinationKey; isset($key); $key = $previous[$key][0] ?? null) {
            $keys[] = $key;
            $passage = $previous[$key][1] ?? null;
            if ($passage !== null) {
                $this->traversedPassages[] = $passage;
            }
            if ($key === $originKey) {
                break;
            }
        }

        $keys = array_reverse($keys);

        return array_map(static fn (string $key): array => Coordinate::fromKey($key), $keys);
    }
}
