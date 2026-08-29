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
use watermossmc\network\mcpe\PacketHandler;
use watermossmc\network\mcpe\protocol\clientbound\ResourcePackStack;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\serverbound\ResourcePackClientResponse;
use watermossmc\network\raknet\RakNet;
use watermossmc\network\Session;
use watermossmc\Server;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class ResourcePackPacketHandler implements PacketHandler
{
    private ?World $world;

    private ?Server $server;

    public function __construct(?World $world, ?Server $server)
    {
        $this->world = $world;
        $this->server = $server;
    }

    public function packetIds(): array
    {
        return [ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET];
    }

    public function handle(string $packet, int $pid, int $offset, Session $session, Socket $socket): bool
    {
        if ($session->getMcpeState() !== Session::MC_RESOURCE) {
            return true;
        }
        $rp = ResourcePackClientResponse::read($packet, $offset);
        $status = $rp['status'];
        Logger::debug("[0x08] ResourcePack Response Status: {$status}");
        match ($status) {
            ResourcePackClientResponse::STATUS_HAVE_ALL_PACKS => (function () use ($session, $socket): void {
                Logger::debug("Client has all packs. Sending stack.");
                ResourcePackStack::send($session, $socket, []);
                RakNet::flush($session, $socket);
            })(),
            ResourcePackClientResponse::STATUS_COMPLETED => (function () use ($session, $socket): void {
                Logger::debug("Resource packs completed. Starting game sequence...");
                $this->startPlay($session, $socket);
            })(),
            default => Logger::debug("Unhandled ResourcePack status: {$status}"),
        };
        return true;
    }

    private function startPlay(Session $s, Socket $sock): void
    {
        $preSpawn = new PreSpawnPacketHandler($this->world, $this->server);
        $preSpawn->triggerSpawnSequence($s, $sock);
    }
}
