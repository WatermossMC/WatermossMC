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

use InvalidArgumentException;

final class AttributeFactory
{
    private static ?AttributeFactory $instance = null;

    /** @var array<string, Attribute> */
    private array $attributes = [];

    private function __construct()
    {
        $this->register('minecraft:health', 0.0, 20.0, 20.0, 20.0);
        $this->register('minecraft:follow_range', 0.0, 2048.0, 32.0, 32.0);
        $this->register('minecraft:knockback_resistance', 0.0, 1.0, 0.0, 0.0);
        $this->register('minecraft:movement', 0.0, 3.4028235E38, 0.1, 0.1);
        $this->register('minecraft:attack_damage', 0.0, 3.4028235E38, 1.0, 1.0);
        $this->register('minecraft:absorption', 0.0, 3.4028235E38, 0.0, 0.0);
        $this->register('minecraft:luck', -1024.0, 1024.0, 0.0, 0.0);
        $this->register('minecraft:player.hunger', 0.0, 20.0, 20.0, 20.0);
        $this->register('minecraft:player.saturation', 0.0, 20.0, 20.0, 20.0);
        $this->register('minecraft:player.exhaustion', 0.0, 5.0, 0.0, 0.0);
        $this->register('minecraft:player.level', 0.0, 24791.0, 0.0, 0.0);
        $this->register('minecraft:player.experience', 0.0, 1.0, 0.0, 0.0);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(string $id, float $min, float $max, float $current, float $default): Attribute
    {
        return $this->attributes[$id] = new Attribute($id, $min, $max, $current, $default);
    }

    public function get(string $id): ?Attribute
    {
        return isset($this->attributes[$id]) ? clone $this->attributes[$id] : null;
    }

    public function mustGet(string $id): Attribute
    {
        $attr = $this->get($id);
        if ($attr === null) {
            throw new InvalidArgumentException("Attribute $id is not registered");
        }
        return $attr;
    }
}
