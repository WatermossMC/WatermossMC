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

namespace watermossmc\block;

use watermossmc\inventory\ItemStack;
use watermossmc\item\ItemRegistry;
use watermossmc\network\mcpe\protocol\clientbound\LevelEvent;
use watermossmc\network\mcpe\protocol\clientbound\UpdateBlock;
use watermossmc\network\mcpe\protocol\types\GameMode;
use watermossmc\network\mcpe\protocol\types\LevelEvent as LevelEventIds;
use watermossmc\player\Player;
use watermossmc\Server;

/**
 * Drives server-authoritative block breaking: starting, progressing, cancelling and completing a
 * break, broadcasting the matching LevelEvent (BLOCK_START_BREAK / BLOCK_BREAK_SPEED /
 * BLOCK_STOP_BREAK) so every nearby player sees the crack animation, and applying the world
 * change once a break actually finishes.
 */
final class BlockBreakManager
{
    /**
     * Player started holding left-click on a block. Records the break and tells every viewer to
     * start showing the crack animation at this position.
     */
    public static function startBreak(Player $player, int $x, int $y, int $z): void
    {
        $block = self::getBlockAt($player, $x, $y, $z);
        if ($block === null || $block->isUnbreakable()) {
            return;
        }

        $breakTime = BreakTimeCalculator::calculate($block);
        $player->setBreakState(new BlockBreakState($x, $y, $z, microtime(true), $breakTime));

        self::broadcastLevelEvent($player, LevelEventIds::BLOCK_START_BREAK, $x, $y, $z, self::breakSpeedEventData($breakTime));
    }

    /**
     * Sent periodically by the client while still mining the same block (ACTION_CONTINUE_BREAK /
     * ACTION_CRACK_BLOCK). Refreshes the crack animation progress for viewers.
     */
    public static function continueBreak(Player $player, int $x, int $y, int $z): void
    {
        $state = $player->getBreakState();
        if ($state === null || !$state->isAtSameBlock($x, $y, $z)) {
            self::startBreak($player, $x, $y, $z);
            return;
        }

        self::broadcastLevelEvent($player, LevelEventIds::BLOCK_BREAK_SPEED, $x, $y, $z, $state->getProgressEventData(microtime(true)));
    }

    /**
     * Player released left-click, switched target, or the client reported the break as done
     * (ACTION_STOP_BREAK / ACTION_ABORT_BREAK). Validates completion against server-side timing
     * rather than trusting the client, then either breaks the block or just cancels the animation.
     */
    public static function stopBreak(Player $player, int $x, int $y, int $z): void
    {
        $state = $player->getBreakState();
        $player->setBreakState(null);

        self::broadcastLevelEvent($player, LevelEventIds::BLOCK_STOP_BREAK, $x, $y, $z, 0);

        if ($state === null || !$state->isAtSameBlock($x, $y, $z)) {
            return;
        }

        if ($state->isComplete(microtime(true))) {
            self::finishBreak($player, $x, $y, $z);
        }
    }

    private static function finishBreak(Player $player, int $x, int $y, int $z): void
    {
        $block = self::getBlockAt($player, $x, $y, $z);
        if ($block === null || $block->isUnbreakable()) {
            return;
        }

        $block->onBreak($x, $y, $z);
        $player->getWorld()->setBlockAt($x, $y, $z, Block::AIR);

        $runtimeId = BlockRuntimeData::getConverter()->toRuntimeId($block);
        self::broadcastLevelEvent($player, LevelEventIds::PARTICLE_DESTROY, $x, $y, $z, $runtimeId);
        self::broadcastBlockUpdate($player, $x, $y, $z);

        if ($player->getGameMode() !== GameMode::CREATIVE) {
            self::dropItems($player, $block, $x, $y, $z);
        }
    }

    private static function dropItems(Player $player, Block $block, int $x, int $y, int $z): void
    {
        foreach ($block->getDrops() as $drop) {
            $item = ItemRegistry::getByIdentifier($drop['identifier']);
            if ($item === null) {
                continue;
            }
            $player->getWorld()->getEntityManager()->spawnItem(new ItemStack($item, $drop['count']), $x + 0.5, $y + 0.25, $z + 0.5);
        }
    }

    private static function getBlockAt(Player $player, int $x, int $y, int $z): ?Block
    {
        $id = $player->getWorld()->getBlockAt($x, $y, $z);
        return BlockRegistry::get($id);
    }

    /**
     * BLOCK_BREAK_SPEED's eventData is the number of ticks the break should take, scaled so the
     * client can locally animate between server updates; 0 duration blocks break in a single hit.
     */
    private static function breakSpeedEventData(float $breakTimeSeconds): int
    {
        return (int) max(1, round($breakTimeSeconds * 20));
    }

    private static function broadcastLevelEvent(Player $origin, int $eventId, int $x, int $y, int $z, int $eventData): void
    {
        $server = Server::getInstance();
        if ($server === null) {
            return;
        }
        foreach ($server->getOnlinePlayers() as $viewer) {
            if ($viewer->getWorld() !== $origin->getWorld()) {
                continue;
            }
            $socket = $viewer->session->getSocket();
            if ($socket === null) {
                continue;
            }
            LevelEvent::send($viewer->session, $socket, $eventId, $x + 0.5, $y + 0.5, $z + 0.5, $eventData);
        }
    }

    public static function broadcastBlockUpdate(Player $origin, int $x, int $y, int $z): void
    {
        $server = Server::getInstance();
        if ($server === null) {
            return;
        }
        $block = BlockRegistry::get($origin->getWorld()->getBlockAt($x, $y, $z)) ?? BlockRegistry::get(Block::AIR);
        $runtimeId = $block !== null ? BlockRuntimeData::getConverter()->toRuntimeId($block) : 0;

        foreach ($server->getOnlinePlayers() as $viewer) {
            if ($viewer->getWorld() !== $origin->getWorld()) {
                continue;
            }
            $socket = $viewer->session->getSocket();
            if ($socket === null) {
                continue;
            }
            UpdateBlock::send($viewer->session, $socket, $x, $y, $z, $runtimeId, 0, 0);
        }
    }
}
