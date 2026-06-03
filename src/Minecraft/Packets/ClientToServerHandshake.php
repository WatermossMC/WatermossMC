<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Util\Logger;

final class ClientToServerHandshake extends Packet
{
    public static function read(string $buf, int &$o): void
    {
        $length = strlen($buf) - $o;
        Logger::debug("[0x04] ClientToServerHandshake payload length={$length}");

        if ($length <= 0) {
            return;
        }

        // Consume any remaining handshake payload without failing the connection.
        // The packet format may vary between protocol versions, so read any remaining bytes safely.
        $remaining = strlen($buf) - $o;
        if ($remaining > 0) {
            $o += $remaining;
        }
    }
}
