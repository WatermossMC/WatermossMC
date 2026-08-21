<?php

declare(strict_types=1);

namespace watermossmc\mcpe\handler;

use RuntimeException;
use Socket;
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
use watermossmc\mcpe\protocol\clientbound\MoveActorAbsolute;
use watermossmc\mcpe\protocol\clientbound\PlayerHotbar;
use watermossmc\mcpe\protocol\clientbound\PlayerList;
use watermossmc\mcpe\protocol\clientbound\PlayStatus;
use watermossmc\mcpe\protocol\clientbound\ResourcePackStack;
use watermossmc\mcpe\protocol\clientbound\SetActorData;
use watermossmc\mcpe\protocol\clientbound\SetSpawnPosition;
use watermossmc\mcpe\protocol\clientbound\SetTime;
use watermossmc\mcpe\protocol\clientbound\StartGame;
use watermossmc\mcpe\protocol\clientbound\UpdateAbilities;
use watermossmc\mcpe\protocol\clientbound\UpdateAdventureSettings;
use watermossmc\mcpe\protocol\clientbound\UpdateAttributes;
use watermossmc\mcpe\protocol\clientbound\VoxelShapes;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\mcpe\protocol\serverbound\ResourcePackClientResponse;
use watermossmc\player\PlayerManager;
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

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool
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
}
