<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\mcpe\network\Session;
use watermossmc\util\Logger;

final class AvailableActorIdentifiers extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $path = \dirname(__DIR__, 3) . '/resources/entity_identifiers.nbt';
        $payload = @file_get_contents($path);

        if ($payload === false) {
            Logger::error("entity_identifiers.nbt not found!");
            return;
        }

        self::sendBatch(ProtocolInfo::AVAILABLE_ACTOR_IDENTIFIERS_PACKET, $payload, $s, $sock);
    }
}
