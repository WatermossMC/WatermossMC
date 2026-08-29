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
use watermossmc\binary\Binary;
use watermossmc\block\BlockRuntimeData;
use watermossmc\event\PlayerMoveEvent;
use watermossmc\network\mcpe\PacketHandler;
use watermossmc\network\mcpe\protocol\clientbound\LevelChunk;
use watermossmc\network\mcpe\protocol\clientbound\PlayStatus;
use watermossmc\network\mcpe\protocol\clientbound\Text;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\serverbound\CommandRequest;
use watermossmc\network\mcpe\protocol\serverbound\MovePlayer;
use watermossmc\network\mcpe\protocol\serverbound\RequestChunkRadius;
use watermossmc\network\raknet\RakNet;
use watermossmc\network\Session;
use watermossmc\player\PlayerManager;
use watermossmc\Server;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class InGamePacketHandler implements PacketHandler
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
        return [
            ProtocolInfo::CLIENT_CACHE_STATUS_PACKET,
            ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET,
            ProtocolInfo::MOVE_PLAYER_PACKET,
            ProtocolInfo::TEXT_PACKET,
            ProtocolInfo::COMMAND_REQUEST_PACKET,
        ];
    }

    public function handle(string $packet, int $pid, int $offset, Session $session, Socket $socket): bool
	{
        switch ($pid) {
            case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET:
                Logger::debug("[0x45] RequestChunkRadius received");
                $data = RequestChunkRadius::read($packet, $offset);
                $session->setChunkRadius($data['radius']);
                $session->setMaxChunkRadius($data['maxRadius']);
                Logger::debug("[0x45] Requested radius={$data['radius']}, maxRadius={$data['maxRadius']}");
                $this->sendSpawnChunks($session, $socket);
                PlayStatus::sendSpawn($session, $socket);
                $session->enterPlay();
                RakNet::flush($session, $socket);
                return true;
            case ProtocolInfo::MOVE_PLAYER_PACKET:
                $moveData = MovePlayer::read($packet, $offset);
                $player = PlayerManager::get($session);
                if ($player === null) {
                    return true;
                }
                $from = $player->getLocation();
                $player->x = $moveData['x'];
                $player->y = $moveData['y'];
                $player->z = $moveData['z'];
                $player->yaw = $moveData['headYaw'];
                $player->pitch = $moveData['pitch'];
                $player->headYaw = $moveData['headYaw'];
                $to = $player->getLocation();
                if ($this->server !== null) {
                    $this->server->dispatch(new PlayerMoveEvent($player, $from, $to));
                }
                return true;
            case ProtocolInfo::TEXT_PACKET:
                Logger::debug("[0x09] Text packet received.");
                $type = Binary::readByte($packet, $offset);
                $needsTranslation = Binary::readBool($packet, $offset);
                $sourceName = "";
                if (\in_array($type, [Text::TYPE_CHAT, Text::TYPE_WHISPER, Text::TYPE_ANNOUNCEMENT], true)) {
                    $sourceName = Binary::readString($packet, $offset);
                    Binary::readString($packet, $offset);
                }
                $message = Binary::readString($packet, $offset);
                Logger::info("[Chat] {$sourceName}: {$message}");
                Binary::readVarInt($packet, $offset);
                Binary::readString($packet, $offset);
                Binary::readString($packet, $offset);
                return true;
            case ProtocolInfo::COMMAND_REQUEST_PACKET:
                Logger::debug("[0x4D] CommandRequest received.");
                $player = PlayerManager::get($session);
                if ($player === null) {
                    return true;
                }
                try {
                    $data = CommandRequest::read($packet, $offset);
                    $command = $data['command'];
                    Logger::info("Player {$player->getUsername()} executed command: {$command}");
                    Server::getInstance()?->dispatchCommand($player, $command);
                } catch (Throwable $e) {
                    Logger::error("Failed to handle CommandRequest: {$e->getMessage()}");
                }
                return true;
        }
        return false;
    }

    private function sendSpawnChunks(Session $session, Socket $socket): void
    {
        $world = $this->world ?? Server::getInstance()?->getWorld();
        if ($world === null) {
            return;
        }
        $spawn = $world->getSpawnPosition();
        $chunkX = $spawn['x'] >> 4;
        $chunkZ = $spawn['z'] >> 4;
        $radius = 4;
        Logger::info("Sending spawn chunks around ({$chunkX}, {$chunkZ}), radius {$radius}...");
        for ($x = -$radius; $x <= $radius; $x++) {
            for ($z = -$radius; $z <= $radius; $z++) {
                $cx = $chunkX + $x;
                $cz = $chunkZ + $z;
                $chunk = $world->getChunk($cx, $cz);
                if ($chunk !== null) {
                    LevelChunk::send($session, $socket, $cx, $cz, $chunk->encode(BlockRuntimeData::getConverter()), $chunk->getSubChunkCount());
                }
				RakNet::flush($session, $socket);
            }
        }
    }
}
