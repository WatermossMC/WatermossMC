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

final class Attribute
{
    public function __construct(
        private string $id,
        private float $min,
        private float $max,
        private float $current,
        private float $default
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getValue(): float
    {
        return $this->current;
    }

    public function setValue(float $value): void
    {
        $this->current = \max($this->min, \min($this->max, $value));
    }

    public function getDefault(): float
    {
        return $this->default;
    }

    public function __clone()
    {
        // Ensure that we are cloning the values
    }
}
