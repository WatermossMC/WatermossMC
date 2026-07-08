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
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\nbt\NBT;

/**
 * EntityData manages the actor properties (Metadata) of an entity.
 * This follows the Bedrock Entity Metadata specification.
 */
final class EntityData
{
    /** @var array<int, array{type: int, value: mixed}> */
    private array $properties = [];

    // Bedrock Metadata Types
    public const TYPE_BYTE = 0;
    public const TYPE_SHORT = 1;
    public const TYPE_INT = 2;
    public const TYPE_FLOAT = 3;
    public const TYPE_STRING = 4;
    public const TYPE_LONG = 7; // Note: Bedrock uses 7 for Long

    public function set(int $id, int $type, mixed $value): void
    {
        $this->properties[$id] = ['type' => $type, 'value' => $value];
    }

    public function setByte(int $id, int $value): void
    {
        $this->set($id, self::TYPE_BYTE, $value);
    }

    public function setShort(int $id, int $value): void
    {
        $this->set($id, self::TYPE_SHORT, $value);
    }

    public function setInt(int $id, int $value): void
    {
        $this->set($id, self::TYPE_INT, $value);
    }

    public function setFloat(int $id, float $value): void
    {
        $this->set($id, self::TYPE_FLOAT, $value);
    }

    public function setString(int $id, string $value): void
    {
        $this->set($id, self::TYPE_STRING, $value);
    }

    public function setLong(int $id, int $value): void
    {
        $this->set($id, self::TYPE_LONG, $value);
    }

    /**
     * Sets a flag in the specified metadata property.
     */
    public function setFlag(int $id, int $flag, bool $value): void
    {
        $currentValue = $this->get($id) ?? 0;
        if ($value) {
            $currentValue |= (1 << $flag);
        } else {
            $currentValue &= ~(1 << $flag);
        }
        $this->setLong($id, $currentValue);
    }

    public function get(int $id): mixed
    {
        return $this->properties[$id]['value'] ?? null;
    }

    /**
     * Encodes properties into a binary blob for SetActorData packet.
     */
    public function encodeMetadata(): string
    {
        $payload = '';
        $payload .= Binary::writeVarInt(\count($this->properties));

        ksort($this->properties);

        foreach ($this->properties as $id => $data) {
            $type = $data['type'];
            $value = $data['value'];

            $payload .= Binary::writeVarInt($id);
            $payload .= Binary::writeVarInt($type);
            $payload .= match ($type) {
                self::TYPE_BYTE => Binary::writeUInt8((int) $value),
                self::TYPE_SHORT => Binary::writeLShort((int) $value),
                self::TYPE_INT => McpeBinary::writeSignedVarInt((int) $value),
                self::TYPE_FLOAT => Binary::writeFloat((float) $value),
                self::TYPE_STRING => McpeBinary::writeString((string) $value),
                self::TYPE_LONG => Binary::writeVarLong((int) $value),
                default => throw new InvalidArgumentException("Unknown metadata type: $type"),
            };
        }
        return $payload;
    }

    /**
     * Encodes properties into an NBT compound for StartGame.
     * Returns the encoded NBT string.
     */
    public function toNbt(): string
    {
        $data = [];
        foreach ($this->properties as $id => $prop) {
            $data[(string)$id] = $prop['value'];
        }
        return NBT::compound($data);
    }

    /**
     * @return array<int, array{type: int, value: mixed}>
     */
    public function getAll(): array
    {
        return $this->properties;
    }
}
