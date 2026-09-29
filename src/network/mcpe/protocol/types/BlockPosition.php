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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\binary\McpeBinary;

final class BlockPosition
{
    public function __construct(
        private int $x,
        private int $y,
        private int $z
    ) {}

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getZ(): int
    {
        return $this->z;
    }

    public static function fromFloats(float $x, float $y, float $z): self
    {
        return new self((int) floor($x), (int) floor($y), (int) floor($z));
    }

    public function equals(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y && $this->z === $other->z;
    }

    public static function read(string $buffer, int &$offset): self
    {
        $x = McpeBinary::readSignedVarInt($buffer, $offset);
        $y = McpeBinary::readSignedVarInt($buffer, $offset);
        $z = McpeBinary::readSignedVarInt($buffer, $offset);
        return new self($x, $y, $z);
    }

    public function write(): string
    {
        return McpeBinary::writeSignedVarInt($this->x)
            . McpeBinary::writeSignedVarInt($this->y)
            . McpeBinary::writeSignedVarInt($this->z);
    }
}
