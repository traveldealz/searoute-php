<?php

declare(strict_types=1);

namespace Searoute;

function searoute(
    array $origin,
    array $destination,
    string $units = 'km',
    float $speedKnot = 24,
    bool $appendOrigDest = false,
    ?array $restrictions = [Passage::NORTHWEST],
    ?Marnet $marnet = null,
    bool $returnPassages = false
): array {
    return Searoute::searoute(
        origin: $origin,
        destination: $destination,
        units: $units,
        speedKnot: $speedKnot,
        appendOrigDest: $appendOrigDest,
        restrictions: $restrictions,
        marnet: $marnet,
        returnPassages: $returnPassages
    );
}

function setup_marnet(?string $path = null): Marnet
{
    return Searoute::setupMarnet($path);
}

function from_nodes_edges_set(Graph $graph, array $nodes, ?array $edges): Graph
{
    return Searoute::fromNodesEdgesSet($graph, $nodes, $edges);
}
