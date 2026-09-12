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

final class PlaySound
{
    public static function send(
        Session $session,
        Socket $socket,
        string $soundName,
        float $x,
        float $y,
        float $z,
        float $volume = 1.0,
        float $pitch = 1.0,
        int $loopCount = 0,
        bool $bypassListenerRangeCheck = false,
        ?int $serverSoundHandle = null,
        ?float $playbackPositionSeconds = null
    ): void {
        $payload = McpeBinary::writeString($soundName);

        $payload .= McpeBinary::writeSignedVarInt((int) ($x * 8));
        $payload .= McpeBinary::writeSignedVarInt((int) ($y * 8));
        $payload .= McpeBinary::writeSignedVarInt((int) ($z * 8));

        $payload .= McpeBinary::writeFloat($volume);
        $payload .= McpeBinary::writeFloat($pitch);
        $payload .= McpeBinary::writeSignedVarInt($loopCount);
        $payload .= McpeBinary::writeBool($bypassListenerRangeCheck);

        if ($serverSoundHandle !== null) {
            $payload .= McpeBinary::writeBool(true);
            $payload .= McpeBinary::writeLong($serverSoundHandle);
        } else {
            $payload .= McpeBinary::writeBool(false);
        }

        if ($playbackPositionSeconds !== null) {
            $payload .= McpeBinary::writeBool(true);
            $payload .= McpeBinary::writeFloat($playbackPositionSeconds);
        } else {
            $payload .= McpeBinary::writeBool(false);
        }

        Packet::sendBatch(
            ProtocolInfo::PLAY_SOUND_PACKET,
            $payload,
            $session,
            $socket
        );
    }
}
