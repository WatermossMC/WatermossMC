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
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

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
    public const CATEGORY_MESSAGE_ONLY = 0;
    public const CATEGORY_AUTHORED_MESSAGE = 1;
    public const CATEGORY_MESSAGE_WITH_PARAMETERS = 2;

    public static function send(
        Session $session,
        Socket $socket,
        string $message,
        int $type = self::TYPE_RAW,
        string $sourceName = "",
        array $parameters = [],
        string $xboxUserId = ""
    ): void {
        $needsTranslation = self::categoryOf($type) === self::CATEGORY_MESSAGE_WITH_PARAMETERS;
        if ($message === "") {
            $message = " ";
        }

        $packet = McpeBinary::writeBool($needsTranslation);
        $packet .= McpeBinary::writeByte(self::categoryOf($type));
        $packet .= McpeBinary::writeByte($type);

        switch ($type) {
            case self::TYPE_CHAT:
            case self::TYPE_WHISPER:
            case self::TYPE_ANNOUNCEMENT:
                $packet .= McpeBinary::writeString($sourceName);
                $packet .= McpeBinary::writeString($message);
                break;
            case self::TYPE_TRANSLATION:
            case self::TYPE_POPUP:
            case self::TYPE_JUKEBOX_POPUP:
                $packet .= McpeBinary::writeString($message);
                $packet .= McpeBinary::writeUnsignedVarInt(\count($parameters));
                foreach ($parameters as $parameter) {
                    $packet .= McpeBinary::writeString($parameter);
                }
                break;
            default:
                $packet .= McpeBinary::writeString($message);
                break;
        }

        $packet .= McpeBinary::writeString($xboxUserId);
        $packet .= McpeBinary::writeString("");
        $packet .= McpeBinary::writeOptional(null);

        Packet::sendBatch(ProtocolInfo::TEXT_PACKET, $packet, $session, $socket);
    }

    public static function categoryOf(int $type): int
    {
        return match ($type) {
            self::TYPE_CHAT, self::TYPE_WHISPER, self::TYPE_ANNOUNCEMENT => self::CATEGORY_AUTHORED_MESSAGE,
            self::TYPE_TRANSLATION, self::TYPE_POPUP, self::TYPE_JUKEBOX_POPUP => self::CATEGORY_MESSAGE_WITH_PARAMETERS,
            default => self::CATEGORY_MESSAGE_ONLY,
        };
    }
}
