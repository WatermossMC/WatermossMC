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

namespace watermossmc\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;

final class UpdateAdventureSettings extends Packet
{
    public static function send(Session $s, Socket $sock, bool $noAttackingMobs, bool $noAttackingPlayers, bool $worldImmutable, bool $showNameTags, bool $autoJump): void
    {
        $payload = '';
        $payload .= Binary::writeBool($noAttackingMobs);
        $payload .= Binary::writeBool($noAttackingPlayers);
        $payload .= Binary::writeBool($worldImmutable);
        $payload .= Binary::writeBool($showNameTags);
        $payload .= Binary::writeBool($autoJump);
        self::sendBatch(ProtocolInfo::UPDATE_ADVENTURE_SETTINGS_PACKET, $payload, $s, $sock);
    }
}
