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
        $packet = Binary::writeVarInt(0x09); // Packet ID
        $packet .= Binary::writeByte($type);
        $packet .= Binary::writeByte(0); // Needs Translation (false)

        switch ($type) {
            case self::TYPE_CHAT:
            case self::TYPE_WHISPER:
            case self::TYPE_ANNOUNCEMENT:
                $packet .= Binary::writeUnsignedVarInt(\strlen($sourceName)) . $sourceName;
                $packet .= Binary::writeUnsignedVarInt(\strlen("")) . ""; // Platform ID
                break;
        }

        $packet .= Binary::writeUnsignedVarInt(\strlen($message)) . $message;
        $packet .= Binary::writeUnsignedVarInt(0);
        $packet .= Binary::writeUnsignedVarInt(\strlen("")) . "";
        $packet .= Binary::writeUnsignedVarInt(\strlen("")) . "";

        $session->sendPacket($packet, $socket);
    }
}
