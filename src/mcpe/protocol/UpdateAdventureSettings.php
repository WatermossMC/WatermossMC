<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class UpdateAdventureSettings extends Packet
{
    public static function send(
        Session $s,
        Socket $sock,
        bool $noAttackingMobs = false,
        bool $noAttackingPlayers = false,
        bool $worldImmutable = false,
        bool $showNameTags = true,
        bool $autoJump = true
    ): void {
        $payload = '';
        $payload .= Binary::writeBool($noAttackingMobs);
        $payload .= Binary::writeBool($noAttackingPlayers);
        $payload .= Binary::writeBool($worldImmutable);
        $payload .= Binary::writeBool($showNameTags);
        $payload .= Binary::writeBool($autoJump);

        self::sendBatch(ProtocolInfo::UPDATE_ADVENTURE_SETTINGS_PACKET, $payload, $s, $sock);
    }
}
