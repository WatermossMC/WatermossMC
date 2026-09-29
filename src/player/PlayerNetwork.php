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

use Socket;
use Throwable;
use watermossmc\network\mcpe\protocol\clientbound\AddPlayer;
use watermossmc\network\mcpe\protocol\clientbound\Animate;
use watermossmc\network\mcpe\protocol\clientbound\LevelSoundEvent;
use watermossmc\network\mcpe\protocol\clientbound\MoveActorAbsolute;
use watermossmc\network\mcpe\protocol\clientbound\PlayerList;
use watermossmc\network\mcpe\protocol\clientbound\RemoveActor;
use watermossmc\network\mcpe\protocol\clientbound\Text;
use watermossmc\network\raknet\RakNet;
use watermossmc\util\Logger;

final class PlayerNetwork
{
    private const POSITION_EPSILON = 0.0001;

    private static array $lastBroadcast = [];

    private static array $announced = [];

    public static function announceJoin(Player $joined): void
    {
        $id = $joined->getRuntimeId();
        if (isset(self::$announced[$id])) {
            return;
        }
        self::$announced[$id] = true;
        self::onJoin($joined);
    }

    public static function onJoin(Player $joined): void
    {
        $joinedSocket = self::socketOf($joined);

        foreach (PlayerManager::all() as $other) {
            if ($other === $joined) {
                continue;
            }

            $otherSocket = self::socketOf($other);

            if ($joinedSocket !== null) {
                self::safely(static function () use ($joined, $joinedSocket, $other): void {
                    PlayerList::sendAdd($joined->session, $joinedSocket, [$other]);
                    AddPlayer::send($joined->session, $joinedSocket, $other);
                    RakNet::flush($joined->session, $joinedSocket);
                });
            }

            if ($otherSocket !== null) {
                self::safely(static function () use ($other, $otherSocket, $joined): void {
                    PlayerList::sendAdd($other->session, $otherSocket, [$joined]);
                    AddPlayer::send($other->session, $otherSocket, $joined);
                    RakNet::flush($other->session, $otherSocket);
                });
            }
        }

        self::broadcastMessage("§e{$joined->getUsername()} joined the game", $joined);
    }

    public static function onQuit(Player $left): void
    {
        $id = $left->getRuntimeId();
        $wasAnnounced = isset(self::$announced[$id]);
        unset(self::$lastBroadcast[$id], self::$announced[$id]);

        if (!$wasAnnounced) {
            return;
        }

        foreach (PlayerManager::all() as $other) {
            if ($other === $left) {
                continue;
            }

            $socket = self::socketOf($other);
            if ($socket === null) {
                continue;
            }

            self::safely(static function () use ($other, $socket, $left): void {
                RemoveActor::send($other->session, $socket, $left->getRuntimeId());
                PlayerList::sendRemove($other->session, $socket, $left->getUuid());
                RakNet::flush($other->session, $socket);
            });
        }

        self::broadcastMessage("§e{$left->getUsername()} left the game", $left);
    }

    public static function broadcastChat(Player $sender, string $message): void
    {
        $line = "<{$sender->getUsername()}> {$message}";
        foreach (PlayerManager::all() as $recipient) {
            $socket = self::socketOf($recipient);
            if ($socket === null) {
                continue;
            }
            self::safely(static function () use ($recipient, $socket, $line): void {
                Text::send($recipient->session, $socket, $line, Text::TYPE_RAW);
                RakNet::flush($recipient->session, $socket);
            });
        }
    }

    public static function broadcastMessage(string $message, ?Player $except = null): void
    {
        foreach (PlayerManager::all() as $recipient) {
            if ($recipient === $except) {
                continue;
            }
            $socket = self::socketOf($recipient);
            if ($socket === null) {
                continue;
            }
            self::safely(static function () use ($recipient, $socket, $message): void {
                Text::send($recipient->session, $socket, $message, Text::TYPE_RAW);
                RakNet::flush($recipient->session, $socket);
            });
        }
    }

    public static function broadcastAnimation(Player $actor, int $action, float $data = 0.0, ?string $swingSource = null): void
    {
        foreach (PlayerManager::all() as $viewer) {
            if ($viewer === $actor) {
                continue;
            }
            $socket = self::socketOf($viewer);
            if ($socket === null) {
                continue;
            }
            self::safely(static function () use ($viewer, $socket, $actor, $action, $data, $swingSource): void {
                Animate::send($viewer->session, $socket, $actor->getRuntimeId(), $action, $data, $swingSource);
                RakNet::flush($viewer->session, $socket);
            });
        }
    }

    public static function broadcastSound(Player $source, string $sound, float $x, float $y, float $z, int $extraData = -1, string $entityType = ':', bool $isBabyMob = false, bool $disableRelativeVolume = false): void
    {
        foreach (PlayerManager::all() as $viewer) {
            if ($viewer === $source) {
                continue;
            }
            $socket = self::socketOf($viewer);
            if ($socket === null) {
                continue;
            }
            self::safely(static function () use ($viewer, $socket, $source, $sound, $x, $y, $z, $extraData, $entityType, $isBabyMob, $disableRelativeVolume): void {
                LevelSoundEvent::send(
                    $viewer->session,
                    $socket,
                    $sound,
                    $x,
                    $y,
                    $z,
                    $extraData,
                    $entityType,
                    $isBabyMob,
                    $disableRelativeVolume,
                    $source->getRuntimeId()
                );
                RakNet::flush($viewer->session, $socket);
            });
        }
    }

    public static function syncMovement(): void
    {
        $players = PlayerManager::all();
        if (\count($players) < 2) {
            return;
        }

        foreach ($players as $mover) {
            if (!$mover->session->isPlaying()) {
                continue;
            }
            if (!self::hasMoved($mover)) {
                continue;
            }

            foreach ($players as $viewer) {
                if ($viewer === $mover) {
                    continue;
                }
                $socket = self::socketOf($viewer);
                if ($socket === null) {
                    continue;
                }
                self::safely(static function () use ($viewer, $socket, $mover): void {
                    MoveActorAbsolute::send(
                        $viewer->session,
                        $socket,
                        $mover,
                        $mover->onGround ? MoveActorAbsolute::FLAG_GROUND : 0,
                        $mover->headYaw
                    );
                    RakNet::flush($viewer->session, $socket);
                });
            }
        }
    }

    public static function applyPendingMove(Player $player): void
    {
        $move = $player->pendingMove;
        if ($move === null) {
            return;
        }
        $player->pendingMove = null;

        $player->x = (float) $move['x'];
        $player->y = (float) $move['y'];
        $player->z = (float) $move['z'];
        $player->yaw = (float) $move['yaw'];
        $player->pitch = (float) $move['pitch'];
        $player->headYaw = (float) $move['headYaw'];
        if (isset($move['onGround'])) {
            $player->onGround = (bool) $move['onGround'];
        }
        $player->session->setPosition($player->x, $player->y, $player->z);
    }

    private static function hasMoved(Player $p): bool
    {
        $id = $p->getRuntimeId();
        $now = ['x' => $p->x, 'y' => $p->y, 'z' => $p->z, 'yaw' => $p->yaw, 'pitch' => $p->pitch, 'headYaw' => $p->headYaw];
        $last = self::$lastBroadcast[$id] ?? null;

        if ($last !== null) {
            $same = true;
            foreach ($now as $key => $value) {
                if (abs($value - $last[$key]) > self::POSITION_EPSILON) {
                    $same = false;
                    break;
                }
            }
            if ($same) {
                return false;
            }
        }

        self::$lastBroadcast[$id] = $now;
        return true;
    }

    private static function socketOf(Player $player): ?Socket
    {
        if (!$player->session->isPlaying()) {
            return null;
        }
        return $player->session->getSocket();
    }

    private static function safely(callable $send): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            Logger::error("[PlayerNetwork] Failed to send packet: {$e->getMessage()}");
        }
    }
}
