<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\entity;

use watermossmc\util\Location;
use watermossmc\world\World;

abstract class Entity
{
    protected int $runtimeId;

    protected string $uuid;

    protected World $world;

    protected EntityData $entityData;

    protected AttributeMap $attributeMap;

    /** @var array<int, array{amplifier: int, particles: bool, duration: int, ambient: bool}> */
    protected array $effects = [];

    public float $x = 0.0;

    public float $y = 0.0;

    public float $z = 0.0;

    public float $pitch = 0.0;

    public float $yaw = 0.0;

    protected float $velocityY = 0.0;

    public function __construct(int $runtimeId, string $uuid, World $world)
    {
        $this->runtimeId = $runtimeId;
        $this->uuid = $uuid;
        $this->world = $world;
        $this->entityData = new EntityData();
        $this->attributeMap = new AttributeMap();
    }

    public function getRuntimeId(): int
    {
        return $this->runtimeId;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEntityData(): EntityData
    {
        return $this->entityData;
    }

    public function getAttributeMap(): AttributeMap
    {
        return $this->attributeMap;
    }

    public function setPosition(float $x, float $y, float $z): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * @return array{x: float, y: float, z: float}
     */
    public function getPosition(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    public function setRotation(float $pitch, float $yaw): void
    {
        $this->pitch = $pitch;
        $this->yaw = $yaw;
    }

    /**
     * @return array{pitch: float, yaw: float}
     */
    public function getRotation(): array
    {
        return ['pitch' => $this->pitch, 'yaw' => $this->yaw];
    }

    public function getLocation(): Location
    {
        return new Location($this->world, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);
    }

    public function tick(): void
    {
        // Basic Gravity Physics
        if (!$this->isOnGround()) {
            $this->velocityY -= 0.08; // Gravity constant
        } else {
            if ($this->velocityY < 0) {
                $this->velocityY = 0;
            }
        }

        $this->y += $this->velocityY;

        // Ground collision
        $surfaceY = $this->world->getSurfaceY(
            (int)round($this->x),
            (int)round($this->z)
        );

        if ($this->y <= $surfaceY) {
            $this->y = (float)$surfaceY;
            $this->velocityY = 0;
        }

        // Update MobEffects duration
        foreach ($this->effects as $id => $effect) {
            if ($effect['duration'] > 0) {
                $this->effects[$id]['duration']--;
                if ($this->effects[$id]['duration'] <= 0) {
                    $this->removeEffect($id);
                }
            }
        }
    }

    public function addEffect(int $effectId, int $amplifier = 0, bool $particles = true, int $duration = 0, bool $ambient = true): void
    {
        $this->effects[$effectId] = [
            'amplifier' => $amplifier,
            'particles' => $particles,
            'duration' => $duration,
            'ambient' => $ambient,
        ];
    }

    public function removeEffect(int $effectId): void
    {
        unset($this->effects[$effectId]);
    }

    /** @return array<int, array{amplifier: int, particles: bool, duration: int, ambient: bool}> */
    public function getEffects(): array
    {
        return $this->effects;
    }

    protected function isOnGround(): bool
    {
        $surfaceY = $this->world->getSurfaceY(
            (int)round($this->x),
            (int)round($this->z)
        );

        return $this->y <= $surfaceY + 0.1;
    }
}
