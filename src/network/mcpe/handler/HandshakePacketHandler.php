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

namespace watermossmc\network\mcpe\handler;

use Socket;
use Throwable;
use watermossmc\network\mcpe\PacketHandler;
use watermossmc\network\mcpe\protocol\clientbound\PlayStatus;
use watermossmc\network\mcpe\protocol\clientbound\ResourcePacksInfo;
use watermossmc\network\mcpe\protocol\handshake\ClientToServerHandshake;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\raknet\RakNet;
use watermossmc\network\Session;
use watermossmc\util\Logger;

final class HandshakePacketHandler implements PacketHandler
{
    public function packetIds(): array
    {
        return [ProtocolInfo::CLIENT_TO_SERVER_HANDSHAKE_PACKET];
    }

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool
    {
        Logger::debug("[0x04] ClientToServerHandshake received.");
        if (!$session->hasWaitingHandshakeAck()) {
            Logger::warning("[0x04] Unexpected packet. Not waiting for ACK. Current state: " . $session->getMcpeState());
            return true;
        }
        try {
            ClientToServerHandshake::read($packet, $offset);
            Logger::debug("[0x04] ClientToServerHandshake parsed successfully");
        } catch (Throwable $e) {
            Logger::error("[0x04] Failed to parse ClientToServerHandshake: {$e->getMessage()}");
            return true;
        }
        $session->setWaitingHandshakeAck(false);
        $session->finalizeEncryption();
        Logger::debug("[0x04] Encryption finalized");
        Logger::info("Encryption ENABLED. Handshake connection secure.");
        PlayStatus::sendSuccess($session, $socket);
        ResourcePacksInfo::send($session, $socket, [], false);
        RakNet::flush($session, $socket);
        $session->setMcpeState(Session::MC_RESOURCE);
        Logger::debug("[0x03] Sent PacksInfo. State -> MC_RESOURCE");
        return true;
    }
}
