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
use watermossmc\binary\McpeBinary;
use watermossmc\block\BlockBreakManager;
use watermossmc\block\BlockPlaceManager;
use watermossmc\block\BlockRegistry;
use watermossmc\block\BlockRuntimeData;
use watermossmc\event\PlayerChatEvent;
use watermossmc\event\PlayerMoveEvent;
use watermossmc\inventory\InventoryRequestExecutor;
use watermossmc\network\mcpe\PacketHandler;
use watermossmc\network\mcpe\protocol\clientbound\Animate as ClientboundAnimate;
use watermossmc\network\mcpe\protocol\clientbound\ChunkRadiusUpdated;
use watermossmc\network\mcpe\protocol\clientbound\ContainerOpen;
use watermossmc\network\mcpe\protocol\clientbound\InventoryContent;
use watermossmc\network\mcpe\protocol\clientbound\ItemStackResponse;
use watermossmc\network\mcpe\protocol\clientbound\LevelChunk;
use watermossmc\network\mcpe\protocol\clientbound\PlayStatus;
use watermossmc\network\mcpe\protocol\clientbound\Respawn as ClientboundRespawn;
use watermossmc\network\mcpe\protocol\clientbound\Text;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\serverbound\Animate as ServerboundAnimate;
use watermossmc\network\mcpe\protocol\serverbound\CommandRequest;
use watermossmc\network\mcpe\protocol\serverbound\ContainerClose as ServerboundContainerClose;
use watermossmc\network\mcpe\protocol\serverbound\InventoryTransaction as ServerboundInventoryTransaction;
use watermossmc\network\mcpe\protocol\serverbound\ItemStackRequest as ServerboundItemStackRequest;
use watermossmc\network\mcpe\protocol\serverbound\LevelSoundEvent as ServerboundLevelSoundEvent;
use watermossmc\network\mcpe\protocol\serverbound\MobEquipment as ServerboundMobEquipment;
use watermossmc\network\mcpe\protocol\serverbound\MovePlayer;
use watermossmc\network\mcpe\protocol\serverbound\PlayerAction;
use watermossmc\network\mcpe\protocol\serverbound\RequestChunkRadius;
use watermossmc\network\mcpe\protocol\serverbound\Respawn as ServerboundRespawn;
use watermossmc\network\mcpe\protocol\serverbound\Text as ServerboundText;
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\network\mcpe\protocol\types\ContainerIds;
use watermossmc\network\raknet\RakNet;
use watermossmc\network\Session;
use watermossmc\player\Player;
use watermossmc\player\PlayerManager;
use watermossmc\player\PlayerNetwork;
use watermossmc\Server;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class InGamePacketHandler implements PacketHandler
{
    /**
     * Fixed window id used for every block UI this server opens (crafting table, etc). Since only
     * one such UI can be open per player at a time and its slots are addressed through the shared
     * UI container (ContainerIds::UI) rather than this id, a single constant is sufficient.
     */
    private const BLOCK_UI_WINDOW_ID = 10;

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
            ProtocolInfo::PLAYER_ACTION_PACKET,
            ProtocolInfo::RESPAWN_PACKET,
            ProtocolInfo::ANIMATE_PACKET,
            ProtocolInfo::LEVEL_SOUND_EVENT_PACKET,
            ProtocolInfo::MOB_EQUIPMENT_PACKET,
            ProtocolInfo::CONTAINER_CLOSE_PACKET,
            ProtocolInfo::ITEM_STACK_REQUEST_PACKET,
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
                ChunkRadiusUpdated::send($session, $socket, $session->getChunkRadius());
                $this->sendSpawnChunks($session, $socket);
                PlayStatus::sendSpawn($session, $socket);
                $session->enterPlay();
                RakNet::flush($session, $socket);
                $joined = PlayerManager::get($session);
                if ($joined !== null) {
                    PlayerNetwork::announceJoin($joined);
                }
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
                try {
                    $text = ServerboundText::read($packet, $offset);
                } catch (Throwable $e) {
                    Logger::warning("Malformed Text packet ignored: {$e->getMessage()}");
                    return true;
                }
                if ($text['type'] === Text::TYPE_CHAT) {
                    $sender = PlayerManager::get($session);
                    $who = $sender?->getUsername() ?? $text['sourceName'];
                    Logger::info("[Chat] {$who}: {$text['message']}");
                    $this->handleChat($sender, $text['message']);
                }
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
            case ProtocolInfo::PLAYER_ACTION_PACKET:
                $data = PlayerAction::read($packet, $offset);
                $player = PlayerManager::get($session);
                if ($player === null) {
                    return true;
                }
                switch ($data['action']) {
                    case PlayerAction::ACTION_RESPAWN:
                        ClientboundRespawn::send($session, $socket, $player->x, $player->y, $player->z, ClientboundRespawn::READY_TO_SPAWN, $player->getRuntimeId());
                        break;
                    case PlayerAction::ACTION_START_BREAK:
                        BlockBreakManager::startBreak($player, $data['blockX'], $data['blockY'], $data['blockZ']);
                        break;
                    case PlayerAction::ACTION_CONTINUE_BREAK:
                    case PlayerAction::ACTION_CONTINUE_DESTROY_BLOCK:
                        BlockBreakManager::continueBreak($player, $data['blockX'], $data['blockY'], $data['blockZ']);
                        break;
                    case PlayerAction::ACTION_ABORT_BREAK:
                    case PlayerAction::ACTION_STOP_BREAK:
                    case PlayerAction::ACTION_PREDICT_DESTROY_BLOCK:
                        BlockBreakManager::stopBreak($player, $data['blockX'], $data['blockY'], $data['blockZ']);
                        break;
                }
                return true;
            case ProtocolInfo::ANIMATE_PACKET:
                $player = PlayerManager::get($session);
                if ($player === null || !$session->isPlaying()) {
                    return true;
                }
                try {
                    $anim = ServerboundAnimate::read($packet, $offset);
                } catch (Throwable $e) {
                    Logger::warning("Malformed Animate packet ignored: {$e->getMessage()}");
                    return true;
                }
                if ($anim['action'] === ClientboundAnimate::ACTION_SWING_ARM) {
                    PlayerNetwork::broadcastAnimation($player, $anim['action'], $anim['data'], $anim['swingSource']);
                }
                return true;
            case ProtocolInfo::LEVEL_SOUND_EVENT_PACKET:
                $player = PlayerManager::get($session);
                if ($player === null || !$session->isPlaying()) {
                    return true;
                }
                try {
                    $snd = ServerboundLevelSoundEvent::read($packet, $offset);
                } catch (Throwable $e) {
                    Logger::warning("Malformed LevelSoundEvent packet ignored: {$e->getMessage()}");
                    return true;
                }
                $dx = $snd['x'] - $player->x;
                $dy = $snd['y'] - $player->y;
                $dz = $snd['z'] - $player->z;
                if (!is_finite($snd['x']) || !is_finite($snd['y']) || !is_finite($snd['z']) || ($dx * $dx + $dy * $dy + $dz * $dz) > 64.0 * 64.0) {
                    return true;
                }
                PlayerNetwork::broadcastSound(
                    $player,
                    $snd['sound'],
                    $snd['x'],
                    $snd['y'],
                    $snd['z'],
                    $snd['extraData'],
                    $snd['entityType'],
                    $snd['isBabyMob'],
                    $snd['disableRelativeVolume']
                );
                return true;
            case ProtocolInfo::MOB_EQUIPMENT_PACKET:
                $this->handleMobEquipment($packet, $offset, $session, $socket);
                return true;
            case ProtocolInfo::CONTAINER_CLOSE_PACKET:
                try {
                    ServerboundContainerClose::read($packet, $offset);
                } catch (Throwable $e) {
                    Logger::warning("Malformed ContainerClose packet ignored: {$e->getMessage()}");
                }
                // Only the default player-owned windows (inventory/armor/offhand) exist right now,
                // and those are never closed server-side, so there is nothing further to do here.
                return true;
            case ProtocolInfo::ITEM_STACK_REQUEST_PACKET:
                $this->handleItemStackRequest($packet, $offset, $session, $socket);
                return true;
            case ProtocolInfo::INVENTORY_TRANSACTION_PACKET:
                $this->handleInventoryTransaction($packet, $offset, $session, $socket);
                return true;
            case ProtocolInfo::RESPAWN_PACKET:
                $data = ServerboundRespawn::read($packet, $offset);
                if ($data['respawnState'] === ServerboundRespawn::CLIENT_READY_TO_SPAWN) {
                    $player = PlayerManager::get($session);
                    if ($player !== null) {
                        ClientboundRespawn::send($session, $socket, $player->x, $player->y, $player->z, ClientboundRespawn::READY_TO_SPAWN, $player->getRuntimeId());
                    }
                }
                return true;
        }
        return false;
    }

    private function handleMobEquipment(string $packet, int $offset, Session $session, Socket $socket): void
    {
        try {
            $equip = ServerboundMobEquipment::read($packet, $offset);
        } catch (Throwable $e) {
            Logger::warning("Malformed MobEquipment packet ignored: {$e->getMessage()}");
            return;
        }

        $player = PlayerManager::get($session);
        if ($player === null) {
            return;
        }

        if ($equip['windowId'] !== ContainerIds::INVENTORY) {
            // Only the main hotbar selection is handled; equipping directly into the offhand slot
            // this way is not supported yet.
            return;
        }

        $player->getInventory()->setSelectedSlot($equip['hotbarSlot']);
    }

    private function handleItemStackRequest(string $packet, int $offset, Session $session, Socket $socket): void
    {
        $player = PlayerManager::get($session);
        if ($player === null) {
            return;
        }

        $requests = [];
        try {
            $count = McpeBinary::readUnsignedVarInt($packet, $offset);
            for ($i = 0; $i < $count; $i++) {
                $requests[] = ServerboundItemStackRequest::read($packet, $offset);
            }
        } catch (Throwable $e) {
            Logger::warning("Malformed ItemStackRequest packet ignored: {$e->getMessage()}");
            return;
        }

        $inventory = $player->getInventory();
        $result = InventoryRequestExecutor::execute($inventory, $requests);

        $accepted = [];
        foreach ($result['accepted'] as $response) {
            $byContainer = [];
            foreach ($response['dirty'] as $ref) {
                $byContainer[$ref['containerId']][$ref['slot']] = true;
            }
            $containers = [];
            foreach ($byContainer as $containerId => $slots) {
                $slotInfos = [];
                foreach (array_keys($slots) as $slot) {
                    $item = $inventory->getItemAt($containerId, $slot);
                    $slotInfos[] = [
                        'slot' => $slot,
                        'count' => $item->count ?? 0,
                        'stackId' => $inventory->getOrAllocateStackId($containerId, $slot),
                    ];
                }
                $containers[] = ['containerId' => $containerId, 'slots' => $slotInfos];
            }
            $accepted[] = ['requestId' => $response['requestId'], 'containers' => $containers];
        }

        ItemStackResponse::send($session, $socket, $accepted, $result['rejected']);
    }

    private function handleChat(?Player $player, string $message): void
    {
        if ($player === null) {
            return;
        }
        $message = trim($message);
        if ($message === '') {
            return;
        }
        $event = new PlayerChatEvent($player, $message);
        if ($this->server !== null) {
            $this->server->dispatch($event);
        }
        if ($event->isCancelled()) {
            return;
        }
        PlayerNetwork::broadcastChat($player, $event->getMessage());
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

    /**
     * Handles InventoryTransactionPacket. Only the "use item on a block" case is acted on: if the
     * targeted block has a UI (e.g. a crafting table), a ContainerOpenPacket is sent so the client
     * shows that UI. The player then drives the crafting grid entirely through
     * ItemStackRequestPacket against the shared UI container, which is already implemented.
     */
    private function handleInventoryTransaction(string $packet, int $offset, Session $session, Socket $socket): void
    {
        $data = ServerboundInventoryTransaction::read($packet, $offset);
        if ($data['transactionType'] !== ServerboundInventoryTransaction::TYPE_USE_ITEM) {
            return;
        }
        if ($data['actionType'] !== ServerboundInventoryTransaction::ACTION_CLICK_BLOCK) {
            return;
        }

        $player = PlayerManager::get($session);
        if ($player === null) {
            return;
        }

        $pos = $data['blockPosition'];
        $blockId = $player->getWorld()->getBlockAt($pos->getX(), $pos->getY(), $pos->getZ());
        $block = BlockRegistry::get($blockId);
        if ($block === null) {
            return;
        }

        $windowType = $block->getContainerWindowType();
        if ($windowType === null) {
            $this->placeHeldBlock($player, $session, $socket, $pos, $data['face']);
            return;
        }

        ContainerOpen::sendForBlock($session, $socket, self::BLOCK_UI_WINDOW_ID, $windowType, $pos->getX(), $pos->getY(), $pos->getZ());
    }

    private function placeHeldBlock(Player $player, Session $session, Socket $socket, BlockPosition $pos, int $face): void
    {
        $placed = BlockPlaceManager::place($player, $pos->getX(), $pos->getY(), $pos->getZ(), $face);

        if ($placed) {
            [$dx, $dy, $dz] = BlockPlaceManager::faceOffset($face);
            BlockBreakManager::broadcastBlockUpdate($player, $pos->getX() + $dx, $pos->getY() + $dy, $pos->getZ() + $dz);
        }

        InventoryContent::sendForInventory($session, $socket, InventoryContent::WINDOW_INVENTORY, $player->getInventory());
    }
}
