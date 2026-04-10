<?php

declare(strict_types=1);

namespace Searoute;

abstract class Graph
{
    protected array $nodes = [];
    protected array $adjacency = [];
    protected KDTree $kdtree;
    protected string $crs = 'EPSG:3857';

    public function __construct()
    {
        $this->kdtree = new KDTree();
    }

    public function addNode(array $node, array $attributes = []): void
    {
        $key = Coordinate::key($node);
        $attributes['x'] = (float) $node[0];
        $attributes['y'] = (float) $node[1];
        $this->nodes[$key] = $attributes;
        $this->adjacency[$key] ??= [];
        $this->kdtree->addPoint([(float) $node[0], (float) $node[1]]);
    }

    public function addEdge(array $from, array $to, array $attributes = []): void
    {
        $fromKey = Coordinate::key($from);
        $toKey = Coordinate::key($to);

        if (!isset($this->nodes[$fromKey])) {
            $this->addNode($from);
        }

        if (!isset($this->nodes[$toKey])) {
            $this->addNode($to);
        }

        if (!array_key_exists('weight', $attributes)) {
            $attributes['weight'] = round(Utils::distance($from, $to), 1);
        }

        $this->adjacency[$fromKey][$toKey] = $attributes;
    }

    public function getNode(array $node): ?array
    {
        return $this->nodes[Coordinate::key($node)] ?? null;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function nearestNode(array $point): array
    {
        return $this->kdtree->query($point);
    }

    public function setData(array $nodes, ?array $adjacency = null): static
    {
        $this->nodes = $nodes;
        $this->adjacency = $adjacency ?? [];
        $this->rebuildKDTree();

        return $this;
    }

    public function rebuildKDTree(?array $keys = null): void
    {
        $points = [];
        foreach ($keys ?? array_keys($this->nodes) as $key) {
            $points[] = Coordinate::fromKey((string) $key);
        }
        $this->kdtree = new KDTree($points);
    }

    public function loadGeoJson(string ...$paths): static
    {
        foreach ($paths as $path) {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            Utils::loadGeoJsonIntoGraph($this, $payload);
        }
        $this->rebuildKDTree();

        return $this;
    }

    protected function subgraph(array $keys): static
    {
        $clone = clone $this;
        $allowed = array_fill_keys($keys, true);
        $clone->nodes = array_intersect_key($this->nodes, $allowed);
        $clone->adjacency = [];

        foreach ($this->adjacency as $fromKey => $edges) {
            if (!isset($allowed[$fromKey])) {
                continue;
            }
            foreach ($edges as $toKey => $attributes) {
                if (isset($allowed[$toKey])) {
                    $clone->adjacency[$fromKey][$toKey] = $attributes;
                }
            }
        }

        $clone->rebuildKDTree();

        return $clone;
    }
}
