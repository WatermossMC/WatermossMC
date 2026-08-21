<?php

declare(strict_types=1);

namespace watermossmc\mcpe\handler;

use Socket;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\PacketHandler;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\handshake\NetworkSettings;
use watermossmc\mcpe\protocol\handshake\RequestNetworkSettings;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\util\Logger;

final class NetworkSettingsPacketHandler implements PacketHandler
{
    public function packetIds(): array
    {
        return [ProtocolInfo::REQUEST_NETWORK_SETTINGS_PACKET];
    }

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool
    {
        Logger::debug("[0xC1] RequestNetworkSettings received.");
        if ($session->getMcpeState() !== Session::MC_NONE) {
            Logger::warning("[0xC1] Ignored: Session state is not NONE.");
            return true;
        }
        if (!RequestNetworkSettings::read($packet, $offset, $session, $socket)) {
            Logger::debug("[0xC1] Protocol mismatch detected. Disconnect sent. Aborting.");
            return true;
        }
        NetworkSettings::send($session, $socket);
        RakNet::flush($session, $socket);
        $session->markNetworkSettingsSent();
        $session->enableOutboundCompression(NetworkSettings::COMPRESS_EVERYTHING);
        $session->enableInboundCompression();
        $session->setMcpeState(Session::MC_NETWORK);
        Logger::debug("[0xC1] Protocol verified. Compression enabled. State -> MC_NETWORK");
        return true;
    }
}
