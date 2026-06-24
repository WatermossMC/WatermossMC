<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Minecraft\NBT\NBT;
use WatermossMC\Network\Session;
use WatermossMC\Util\Logger;

final class AvailableActorIdentifiers extends Packet
{
    public static function send(Session $s, Socket $sock): void
  {
        $path = dirname(__DIR__, 3) . '/resources/entity_identifiers.nbt';
        $payload = @file_get_contents($path);

        if ($payload === false) {
            Logger::error("entity_identifiers.nbt not found!");
            return;
        }

        self::sendBatch(ProtocolInfo::AVAILABLE_ACTOR_IDENTIFIERS_PACKET, $payload, $s, $sock);
  }
}