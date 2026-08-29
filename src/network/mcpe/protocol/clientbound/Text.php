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

namespace watermossmc\network\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\network\Session;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;

final class Text
{
    public const TYPE_RAW = 0;
    public const TYPE_CHAT = 1;
    public const TYPE_TRANSLATION = 2;
    public const TYPE_POPUP = 3;
    public const TYPE_JUKEBOX_POPUP = 4;
    public const TYPE_TIP = 5;
    public const TYPE_SYSTEM = 6;
    public const TYPE_WHISPER = 7;
    public const TYPE_ANNOUNCEMENT = 8;
    public const TYPE_JSON_WHISPER = 9;
    public const TYPE_JSON = 10;
    public const TYPE_JSON_ANNOUNCEMENT = 11;

    public static function send(
        Session $session,
        Socket $socket,
        string $message,
        int $type = self::TYPE_RAW,
        string $sourceName = ""
    ): void {
        $packet = Binary::writeByte($type);
        $packet .= Binary::writeByte(0); // Needs Translation (false)

        switch ($type) {
            case self::TYPE_CHAT:
            case self::TYPE_WHISPER:
            case self::TYPE_ANNOUNCEMENT:
                $packet .= Binary::writeVarInt(\strlen($sourceName)) . $sourceName;
                $packet .= Binary::writeVarInt(\strlen("")) . ""; // Platform ID
                break;
        }

        $packet .= Binary::writeVarInt(\strlen($message)) . $message;
        $packet .= Binary::writeVarInt(0);
        $packet .= Binary::writeVarInt(\strlen("")) . "";
        $packet .= Binary::writeVarInt(\strlen("")) . "";

        Packet::sendBatch(ProtocolInfo::TEXT_PACKET, $packet, $session, $socket);
    }
}
