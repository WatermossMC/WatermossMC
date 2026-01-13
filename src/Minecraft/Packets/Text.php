<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

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

        Packet::sendBatch(0x09, $packet, $session, $socket);
    }
}
