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

namespace watermossmc\mcpe\handler;

use Socket;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\PacketHandler;
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
