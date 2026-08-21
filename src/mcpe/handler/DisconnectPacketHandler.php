<?php

declare(strict_types=1);

namespace watermossmc\mcpe\handler;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\event\PlayerQuitEvent;
use watermossmc\mcpe\PacketHandler;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\clientbound\Disconnect;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\player\PlayerManager;
use watermossmc\Server;
use watermossmc\util\Logger;

final class DisconnectPacketHandler implements PacketHandler
{
    public function packetIds(): array
    {
        return [ProtocolInfo::DISCONNECT_PACKET];
    }

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool
    {
        Logger::debug("[0x05] Disconnect packet received.");
        $reason = Binary::readString($packet, $offset);
        Logger::info("Player disconnected: {$reason}");
        $player = PlayerManager::get($session);
        if ($player !== null) {
            Server::getInstance()?->dispatch(new PlayerQuitEvent($player));
            PlayerManager::remove($session);
        }
        $session->close();
        return true;
    }
}
