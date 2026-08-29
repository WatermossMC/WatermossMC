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

namespace watermossmc\player;

use watermossmc\command\CommandSender;
use watermossmc\entity\AttributeFactory;
use watermossmc\entity\Entity;
use watermossmc\entity\EntityMetadataProperties;
use watermossmc\inventory\PlayerInventory;
use watermossmc\network\Session;
use watermossmc\network\mcpe\protocol\clientbound\Disconnect;
use watermossmc\network\mcpe\protocol\clientbound\MobEffect;
use watermossmc\network\mcpe\protocol\clientbound\Text;
use watermossmc\Server;
use watermossmc\util\Permission;

final class Player extends Entity implements CommandSender
{
    public Session $session;

    public string $uuid;

    public string $username;

    public int $runtimeId;

    public float $x = 0;

    public float $y = 64;

    public float $z = 0;

    public float $yaw = 0;

    public float $pitch = 0;

    public float $headYaw = 0;

    public bool $onGround = true;

    private Server $server;

    /** @var array<string, float|int|bool>|null */
    public ?array $pendingMove = null;

    public PlayerInventory $inventory;

    private int $gameMode = 0;

    private int $role = Permission::ROLE_MEMBER;

    public function __construct(Session $s, string $username, Server $server)
    {
        parent::__construct($s->getRuntimeId(), $s->getUuid(), $server->getWorld());
        $this->session = $s;
        $this->uuid = $s->getUuid();
        $this->username = $username;
        $this->inventory = new PlayerInventory();
        $factory = AttributeFactory::getInstance();
        $map = $this->getAttributeMap();
        $map->add($factory->mustGet('minecraft:health'));
        $map->add($factory->mustGet('minecraft:follow_range'));
        $map->add($factory->mustGet('minecraft:knockback_resistance'));
        $map->add($factory->mustGet('minecraft:movement'));
        $map->add($factory->mustGet('minecraft:attack_damage'));
        $map->add($factory->mustGet('minecraft:absorption'));
        $map->add($factory->mustGet('minecraft:luck'));
        $map->add($factory->mustGet('minecraft:player.hunger'));
        $map->add($factory->mustGet('minecraft:player.saturation'));
        $map->add($factory->mustGet('minecraft:player.exhaustion'));
        $map->add($factory->mustGet('minecraft:player.level'));
        $map->add($factory->mustGet('minecraft:player.experience'));
        // Load role from OperatorManager
        $this->role = OperatorManager::getPermissionLevel($username);
        // Initialize default player properties
        $this->getEntityData()->setString(EntityMetadataProperties::NAMETAG, $username);
    }

    public function getName(): string
    {
        return $this->username;
    }

    public function getUniqueId(): string
    {
        return $this->uuid;
    }

    public function getRuntimeId(): int
    {
        return $this->runtimeId;
    }

    public function teleport(float $x, float $y, float $z, ?float $yaw = null, ?float $pitch = null): void
    {
        $this->setPosition($x, $y, $z);
        $this->setRotation($yaw ?? $this->yaw, $pitch ?? $this->pitch);
        $this->session->setPosition($x, $y, $z);
    }

    public function sendMessage(string $message, int $type = Text::TYPE_RAW): void
    {
        $socket = $this->session->getSocket();
        if ($socket === null || !$this->session->isPlaying()) {
            return;
        }
        Text::send($this->session, $socket, $message, $type);
    }

    public function kick(string $reason = "Disconnected"): bool
    {
        $socket = $this->session->getSocket();
        if ($socket === null) {
            $this->session->close(false);
            return false;
        }
        Disconnect::send($this->session, $socket, $reason);
        $this->session->close(false);
        return true;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getGameMode(): int
    {
        return $this->gameMode;
    }

    public function setGameMode(int $gameMode): void
    {
        $this->gameMode = $gameMode;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    public function hasPermission(int $role): bool
    {
        return $this->role >= $role;
    }

    public function tick(): void
    {
        parent::tick();
    }

    public function applyEffect(int $effectId, int $amplifier = 0, bool $particles = true, int $duration = 0, bool $ambient = true): void
    {
        $this->addEffect($effectId, $amplifier, $particles, $duration, $ambient);
        $socket = $this->session->getSocket();
        if ($socket !== null) {
            MobEffect::add($this->session, $socket, $effectId, $amplifier, $particles, $duration, $ambient);
        }
    }

    public function removeEffect(int $effectId): void
    {
        parent::removeEffect($effectId);
        $socket = $this->session->getSocket();
        if ($socket !== null) {
            MobEffect::remove($this->session, $socket, $effectId);
        }
    }
}
