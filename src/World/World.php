<?php

namespace WatermossMC\World;

class World
{
    private $entities = [];
    private $seed;

    public function __construct(string $path, int $seed)
    {
        echo "Loading world from $path with seed $seed...\n";
        $this->seed = $seed;
    }

    public function spawnEntity(float $x, float $y, float $z): Entity
    {
        $id = count($this->entities) + 1;
        $entity = new Entity($id, $x, $y, $z);
        $this->entities[$id] = $entity;
        echo "Spawned entity #$id at ($x, $y, $z)\n";
        return $entity;
    }

    public function tick()
    {
        foreach ($this->entities as $entity) {
            // Example: Apply gravity
            $entity->move(0, -0.1, 0);
        }
    }
}
