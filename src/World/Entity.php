<?php

namespace WatermossMC\World;

class Entity
{
    private $id;
    private $x;
    private $y;
    private $z;

    public function __construct(int $id, float $x, float $y, float $z)
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function move(float $dx, float $dy, float $dz)
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
}
