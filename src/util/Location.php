<?php

declare(strict_types=1);

namespace watermossmc\util;

use watermossmc\world\World;

final class Location
{
    public function __construct(
        public readonly World $world,
        public float $x,
        public float $y,
        public float $z,
        public float $yaw = 0,
        public float $pitch = 0
    ) {}

    /**
     * @return array{x: float, y: float, z: float, yaw: float, pitch: float}
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
            'yaw' => $this->yaw,
            'pitch' => $this->pitch,
        ];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public function blockToInteger(): array
    {
        return [
            (int) floor($this->x),
            (int) floor($this->y),
            (int) floor($this->z),
        ];
    }

    public function __toString(): string
    {
        return \sprintf(
            '%s (%.2f, %.2f, %.2f)',
            $this->world->getName(),
            $this->x,
            $this->y,
            $this->z
        );
    }
}
