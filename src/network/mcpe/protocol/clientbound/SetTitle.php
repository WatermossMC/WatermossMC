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

final class SetTitle
{
    public const TYPE_CLEAR_TITLE = 0;
    public const TYPE_RESET_TITLE = 1;
    public const TYPE_SET_TITLE = 2;
    public const TYPE_SET_SUBTITLE = 3;
    public const TYPE_SET_ACTIONBAR_MESSAGE = 4;
    public const TYPE_SET_ANIMATION_TIMES = 5;
    public const TYPE_SET_TITLE_JSON = 6;
    public const TYPE_SET_SUBTITLE_JSON = 7;
    public const TYPE_SET_ACTIONBAR_MESSAGE_JSON = 8;

    public static function send(
        Session $s,
        Socket $sock,
        int $type,
        string $text = '',
        int $fadeInTime = -1,
        int $stayTime = -1,
        int $fadeOutTime = -1
    ): void {
        $p = McpeBinary::writeSignedVarInt($type);
        $p .= McpeBinary::writeString($text);
        $p .= McpeBinary::writeSignedVarInt($fadeInTime);
        $p .= McpeBinary::writeSignedVarInt($stayTime);
        $p .= McpeBinary::writeSignedVarInt($fadeOutTime);
        $p .= McpeBinary::writeString('');
        $p .= McpeBinary::writeString('');
        $p .= McpeBinary::writeString($text);
        Packet::sendBatch(ProtocolInfo::SET_TITLE_PACKET, $p, $s, $sock);
    }

    public static function title(Session $s, Socket $sock, string $title, string $subtitle = ''): void
    {
        self::send($s, $sock, self::TYPE_SET_TITLE, $title);
        if ($subtitle !== '') {
            self::send($s, $sock, self::TYPE_SET_SUBTITLE, $subtitle);
        }
    }

    public static function actionBar(Session $s, Socket $sock, string $message): void
    {
        self::send($s, $sock, self::TYPE_SET_ACTIONBAR_MESSAGE, $message);
    }
}
