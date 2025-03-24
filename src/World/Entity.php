<?php

namespace WatermossMC\World;

class Entity
{
    private int $id;
    private float|int $x;
    private float|int $y;
    private float|int $z;

    public function __construct(int $id, float|int $x, float|int $y, float|int $z)
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function move(float|int $dx, float|int $dy, float|int $dz): void
    {
        $this->x += $dx;
        $this->y += $dy;
        $this->z += $dz;
    }

    public function getPosition(): array
    {
        return [$this->x, $this->y, $this->z];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getX(): float|int
    {
        return $this->x;
    }

    public function getY(): float|int
    {
        return $this->y;
    }

    public function getZ(): float|int
    {
        return $this->z;
    }
}
