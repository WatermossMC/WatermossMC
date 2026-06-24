<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class PlayerHotbar extends Packet
{
    public const WINDOW_INVENTORY = 0;

    public static function send(
        Session $s,
        Socket $sock,
        int $selectedSlot = 0,
        int $windowId = self::WINDOW_INVENTORY,
        bool $selectHotbarSlot = true
    ): void {
        $payload = '';
        $payload .= Binary::writeVarInt($selectedSlot);   // selectedHotbarSlot (varuint)
        $payload .= Binary::writeUInt8($windowId);        // windowId (unsigned byte)
        $payload .= Binary::writeBool($selectHotbarSlot); // selectHotbarSlot

        self::sendBatch(ProtocolInfo::PLAYER_HOTBAR_PACKET, $payload, $s, $sock);
    }
}