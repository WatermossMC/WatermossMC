<?php

declare(strict_types=1);

namespace watermossmc\mcpe\handler;

use Socket;
use Throwable;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\PacketHandler;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\clientbound\PlayStatus;
use watermossmc\mcpe\protocol\clientbound\ResourcePacksInfo;
use watermossmc\mcpe\protocol\handshake\ClientToServerHandshake;
use watermossmc\mcpe\protocol\ProtocolInfo;
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
