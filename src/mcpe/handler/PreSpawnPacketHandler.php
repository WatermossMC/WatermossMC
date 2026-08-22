<?php

declare(strict_types=1);

namespace watermossmc\mcpe\handler;

use RuntimeException;
use Socket;
use Throwable;
use watermossmc\binary\Binary;
use watermossmc\event\PlayerJoinEvent;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\PacketHandler;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\clientbound\AddPlayer;
use watermossmc\mcpe\protocol\clientbound\AvailableActorIdentifiers;
use watermossmc\mcpe\protocol\clientbound\AvailableCommands;
use watermossmc\mcpe\protocol\clientbound\BiomeDefinitionList;
use watermossmc\mcpe\protocol\clientbound\CraftingData;
use watermossmc\mcpe\protocol\clientbound\CreativeContent;
use watermossmc\mcpe\protocol\clientbound\InventoryContent;
use watermossmc\mcpe\protocol\clientbound\ItemRegistry;
use watermossmc\mcpe\protocol\clientbound\LevelChunk;
use watermossmc\mcpe\protocol\clientbound\MobEffect;
use watermossmc\mcpe\protocol\clientbound\PlayerHotbar;
use watermossmc\mcpe\protocol\clientbound\PlayerList;
use watermossmc\mcpe\protocol\clientbound\PlayStatus;
use watermossmc\mcpe\protocol\clientbound\SetActorData;
use watermossmc\mcpe\protocol\clientbound\SetSpawnPosition;
use watermossmc\mcpe\protocol\clientbound\SetTime;
use watermossmc\mcpe\protocol\clientbound\StartGame;
use watermossmc\mcpe\protocol\clientbound\UpdateAbilities;
use watermossmc\mcpe\protocol\clientbound\UpdateAdventureSettings;
use watermossmc\mcpe\protocol\clientbound\UpdateAttributes;
use watermossmc\mcpe\protocol\clientbound\VoxelShapes;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\mcpe\protocol\serverbound\PlayerAuthInput;
use watermossmc\mcpe\protocol\serverbound\RequestChunkRadius;
use watermossmc\player\PlayerManager;
use watermossmc\Server;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class PreSpawnPacketHandler implements PacketHandler
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
            ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET,
            ProtocolInfo::PLAYER_AUTH_INPUT_PACKET,
        ];
    }

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool
    {
        $pid = Binary::readVarInt($packet, $offset);
        switch ($pid) {
            case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET:
                Logger::debug("[0x45] RequestChunkRadius received");
                $data = RequestChunkRadius::read($packet, $offset);
                $session->setChunkRadius($data['radius']);
                $session->setMaxChunkRadius($data['maxRadius']);
                Logger::debug("[0x45] Requested radius={$data['radius']}, maxRadius={$data['maxRadius']}");
                $this->sendSpawnChunks($session, $socket);
                PlayStatus::sendPlayerSpawn($session, $socket);
                $session->enterPlay();
                RakNet::flush($session, $socket);
                return true;
            case ProtocolInfo::PLAYER_AUTH_INPUT_PACKET:
                if ($session->getMcpeState() !== Session::MC_PRESPAWN && $session->getMcpeState() !== Session::MC_PLAY) {
                    return false;
                }
                try {
                    PlayerAuthInput::handle($packet, $session);
                    if ($session->getMcpeState() === Session::MC_PRESPAWN) {
                        $session->enterPlay();
                        Logger::debug("[0x94] PlayerAuthInput received in MC_PRESPAWN -> transitioned session to MC_PLAY.");
                    }
                    return true;
                } catch (Throwable $e) {
                    Logger::error("Failed to handle PlayerAuthInput in PreSpawnPacketHandler: {$e->getMessage()}");
                    return true;
                }
        }
        return false;
    }

    public function triggerSpawnSequence(Session $s, Socket $sock): void
    {
        Logger::info("Starting game sequence for " . $s->getPlayerName());
        $world = $this->world ?? World::getDefault();
        if ($world === null) {
            throw new RuntimeException("World not available");
        }
        if ($this->server === null) {
            throw new RuntimeException("Server not initialized");
        }
        $player = PlayerManager::add($s, $s->getPlayerName(), $this->server);
        $world->getEntityManager()->addEntity($player);
        $this->server->dispatch(new PlayerJoinEvent($this->server, $player));
        $s->setMcpeState(Session::MC_PRESPAWN);
        PlayStatus::sendSpawn($s, $sock);
        StartGame::send($s, $sock, $world);
        BiomeDefinitionList::send($s, $sock);
        AvailableActorIdentifiers::send($s, $sock);
        VoxelShapes::send($s, $sock);
        ItemRegistry::send($s, $sock);
        CraftingData::send($s, $sock);
        CreativeContent::send($s, $sock);
        AvailableCommands::send($s, $sock);
        PlayerList::sendAdd($s, $sock, [$player]);
        SetSpawnPosition::send($s, $sock, $world->getSpawnPosition());
        SetTime::send($s, $sock, 0);
        UpdateAbilities::send($s, $sock);
        UpdateAdventureSettings::send($s, $sock);
        InventoryContent::sendInitial($s, $sock);
        PlayerHotbar::sendInitial($s, $sock);
        SetActorData::sendSelf($s, $sock, $player);
        UpdateAttributes::sendSelf($s, $sock, $player);
        AddPlayer::send($s, $sock, $player);
        MobEffect::sendInitial($s, $sock);
        RakNet::flush($s, $sock);
    }

    private function sendSpawnChunks(Session $session, Socket $socket): void
    {
        $world = $this->world ?? World::getDefault();
        if ($world === null) {
            return;
        }
        $spawn = $world->getSpawnPosition();
        $chunkX = $spawn->getFloorX() >> 4;
        $chunkZ = $spawn->getFloorZ() >> 4;
        $radius = 4;
        Logger::info("Sending spawn chunks around ({$chunkX}, {$chunkZ}), radius {$radius}...");
        for ($x = -$radius; $x <= $radius; $x++) {
            for ($z = -$radius; $z <= $radius; $z++) {
                $cx = $chunkX + $x;
                $cz = $chunkZ + $z;
                $chunk = $world->getChunk($cx, $cz);
                if ($chunk !== null) {
                    LevelChunk::send($session, $socket, $chunk);
                }
            }
        }
    }
}
