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

use RuntimeException;
use watermossmc\binary\McpeBinary;

final class ItemStackDescriptor
{
    private const MAX_EXTRA_DATA = 65535;

    public function __construct(
        public readonly int $id,
        public readonly int $count,
        public readonly int $meta,
        public readonly int $stackId,
        public readonly int $blockRuntimeId,
        public readonly string $rawExtraData
    ) {}

    public static function air(): self
    {
        return new self(0, 0, 0, 0, 0, '');
    }

    public function isAir(): bool
    {
        return $this->id === 0;
    }

    public static function read(string $buffer, int &$offset): self
    {
        $id = McpeBinary::readSignedLShort($buffer, $offset);
        $count = McpeBinary::readUnsignedLShort($buffer, $offset);
        $meta = McpeBinary::readUnsignedVarInt($buffer, $offset);

        $stackId = McpeBinary::readBool($buffer, $offset)
            ? McpeBinary::readSignedVarInt($buffer, $offset)
            : 0;

        $rawBlockRuntimeId = McpeBinary::readUnsignedVarInt($buffer, $offset);
        $blockRuntimeId = $rawBlockRuntimeId >= 0x80000000 ? $rawBlockRuntimeId - 0x100000000 : $rawBlockRuntimeId;

        $rawExtraData = McpeBinary::readString($buffer, $offset);
        if (\strlen($rawExtraData) > self::MAX_EXTRA_DATA) {
            throw new RuntimeException('Item extra data too large: ' . \strlen($rawExtraData));
        }

        return new self($id, $count, $meta, $stackId, $blockRuntimeId, $rawExtraData);
    }

    public function write(): string
    {
        $out = McpeBinary::writeLShort($this->id);
        $out .= McpeBinary::writeLShort($this->count);
        $out .= McpeBinary::writeUnsignedVarInt($this->meta);
        if ($this->stackId !== 0) {
            $out .= McpeBinary::writeBool(true);
            $out .= McpeBinary::writeSignedVarInt($this->stackId);
        } else {
            $out .= McpeBinary::writeBool(false);
        }
        $out .= McpeBinary::writeUnsignedVarInt($this->blockRuntimeId & 0xFFFFFFFF);
        $out .= McpeBinary::writeString($this->rawExtraData);
        return $out;
    }
}
