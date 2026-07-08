<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
<<<<<<< HEAD
use watermossmc\mcpe\network\Session;

final class AddPlayer extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $pos = $s->getPosition();
        $rot = $s->getRotation();

        $p = Binary::writeByte(0x0c);
        $p .= Binary::writeUUID($s->getUuid());
        $p .= Binary::writeString($s->getUsername());
        $p .= Binary::writeVarLong($s->getRuntimeId());
        $p .= Binary::writeString("");
=======
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;
use watermossmc\player\Player;

final class AddPlayer extends Packet
{
    public static function send(Session $s, Socket $sock, Player $target): void
    {
        $pos = $target->getPosition();
        $rot = $target->getRotation();

        $p = Binary::writeByte(0x0c);
        $p .= Binary::writeUUID($target->getUuid());
        $p .= McpeBinary::writeString($target->getUsername());
        $p .= Binary::writeVarLong($target->getRuntimeId());
        $p .= McpeBinary::writeString("");
>>>>>>> 866a1c0 (...)

        $p .= Binary::writeFloat($pos['x']);
        $p .= Binary::writeFloat($pos['y']);
        $p .= Binary::writeFloat($pos['z']);

        $p .= Binary::writeFloat(0.0);
        $p .= Binary::writeFloat(0.0);
        $p .= Binary::writeFloat(0.0);

        $p .= Binary::writeFloat($rot['pitch']);
        $p .= Binary::writeFloat($rot['yaw']);
        $p .= Binary::writeFloat($rot['yaw']);

        $p .= Binary::writeVarInt(0); // air item stack wrapper
<<<<<<< HEAD
        $p .= Binary::writeVarInt($s->getGameMode());
=======
        $p .= McpeBinary::writeSignedVarInt($target->getGameMode());
>>>>>>> 866a1c0 (...)

        $p .= Binary::writeVarInt(0); // metadata count
        $p .= Binary::writeVarInt(0); // synced int properties
        $p .= Binary::writeVarInt(0); // synced float properties

        $p .= Binary::writeLong(0); // target actor unique id for abilities
        $p .= Binary::writeByte(1); // player permission
        $p .= Binary::writeByte(0); // command permission
        $p .= Binary::writeByte(0); // ability layer count
        $p .= Binary::writeVarInt(0); // link count
<<<<<<< HEAD
        $p .= Binary::writeString(""); // device id
=======
        $p .= McpeBinary::writeString(""); // device id
>>>>>>> 866a1c0 (...)
        $p .= Binary::writeLInt(0); // build platform
        self::sendBatch(ProtocolInfo::ADD_PLAYER_PACKET, $p, $s, $sock);
    }
}
