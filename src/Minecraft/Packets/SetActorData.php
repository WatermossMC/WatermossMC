<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Network\Session;

final class SetActorData extends Packet
{
    // Metadata keys
    public const DATA_FLAGS         = 0;
    public const DATA_NAMETAG       = 4;
    public const DATA_AIR           = 7;
    public const DATA_MAX_AIR       = 42;
    public const DATA_BOUNDING_BOX_WIDTH  = 54;
    public const DATA_BOUNDING_BOX_HEIGHT = 55;

    // Metadata types
    public const TYPE_BYTE   = 0;
    public const TYPE_SHORT  = 1;
    public const TYPE_INT    = 2;
    public const TYPE_FLOAT  = 3;
    public const TYPE_STRING = 4;
    public const TYPE_LONG   = 7;

    // Entity flags (bit positions in DATA_FLAGS long)
    public const FLAG_NO_AI        = 16;
    public const FLAG_CAN_CLIMB    = 19;
    public const FLAG_BREATHING    = 35;

    public static function sendPlayer(Session $s, Socket $sock): void
    {
        // flags: breathing + can climb
        $flags = 0;
        $flags |= (1 << self::FLAG_BREATHING);
        $flags |= (1 << self::FLAG_CAN_CLIMB);

        $entries = [
            [self::DATA_FLAGS,              self::TYPE_LONG,  $flags],
            [self::DATA_AIR,                self::TYPE_SHORT, 400],
            [self::DATA_MAX_AIR,            self::TYPE_SHORT, 400],
            [self::DATA_BOUNDING_BOX_WIDTH,  self::TYPE_FLOAT, 0.6],
            [self::DATA_BOUNDING_BOX_HEIGHT, self::TYPE_FLOAT, 1.8],
        ];

        self::send($s, $sock, $entries);
    }

    /**
     * @param array<int, array{int, int, mixed}> $entries  [key, type, value]
     */
    public static function send(Session $s, Socket $sock, array $entries, int $tick = 0): void
    {
        $payload = '';
        $payload .= Binary::writeUnsignedVarLong($s->getRuntimeId()); // actorRuntimeId

        // metadata dictionary
        $payload .= Binary::writeVarInt(count($entries));
        foreach ($entries as [$key, $type, $value]) {
            $payload .= Binary::writeVarInt($key);
            $payload .= Binary::writeVarInt($type);
            $payload .= match ($type) {
                self::TYPE_BYTE   => Binary::writeUInt8((int) $value),
                self::TYPE_SHORT  => Binary::writeLShort((int) $value),
                self::TYPE_INT    => McpeBinary::writeSignedVarInt((int) $value),
                self::TYPE_FLOAT  => Binary::writeFloat((float) $value),
                self::TYPE_STRING => McpeBinary::writeString((string) $value),
                self::TYPE_LONG   => Binary::writeVarLong((int) $value),
                default           => throw new \InvalidArgumentException("Unknown metadata type: $type"),
            };
        }

        // PropertySyncData: intProperties count + floatProperties count (both 0)
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);

        // tick
        $payload .= Binary::writeVarLong($tick);

        self::sendBatch(ProtocolInfo::SET_ACTOR_DATA_PACKET, $payload, $s, $sock);
    }
}