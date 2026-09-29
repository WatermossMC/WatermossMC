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

use watermossmc\network\mcpe\protocol\types\GameMode;
use watermossmc\player\Player;

final class BlockPlaceManager
{
    private const MIN_Y = -64;
    private const MAX_Y = 319;
    private const PLAYER_WIDTH = 0.6;
    private const PLAYER_HEIGHT = 1.8;
    private const PLAYER_EYE_HEIGHT = 1.62;

    /**
     * Bedrock block face index => [dx, dy, dz] of the neighbouring block that face points to.
     */
    private const FACE_OFFSETS = [
        0 => [0, -1, 0],
        1 => [0, 1, 0],
        2 => [0, 0, -1],
        3 => [0, 0, 1],
        4 => [-1, 0, 0],
        5 => [1, 0, 0],
    ];

    /**
     * @return array{int, int, int}
     */
    public static function faceOffset(int $face): array
    {
        return self::FACE_OFFSETS[$face] ?? [0, 0, 0];
    }

    public static function place(Player $player, int $x, int $y, int $z, int $face): bool
    {
        $offset = self::FACE_OFFSETS[$face] ?? null;
        if ($offset === null) {
            return false;
        }

        $held = $player->getInventory()->getSelectedItem();
        $blockId = $held?->item->getBlockId();
        if ($held === null || $held->isEmpty() || $blockId === null) {
            return false;
        }

        $block = BlockRegistry::get($blockId);
        if ($block === null) {
            return false;
        }

        [$tx, $ty, $tz] = [$x + $offset[0], $y + $offset[1], $z + $offset[2]];
        if ($ty < self::MIN_Y || $ty > self::MAX_Y) {
            return false;
        }

        $world = $player->getWorld();
        if ($world->getBlockAt($tx, $ty, $tz) !== Block::AIR || self::intersectsPlayer($player, $tx, $ty, $tz)) {
            return false;
        }

        $world->setBlockAt($tx, $ty, $tz, $blockId);
        $block->onPlace($tx, $ty, $tz);

        if ($player->getGameMode() !== GameMode::CREATIVE) {
            $slot = $player->getInventory()->getSelectedSlot();
            $left = $held->withCountRemoved(1);
            $player->getInventory()->setItem($slot, $left->isEmpty() ? null : $left);
        }

        return true;
    }

    private static function intersectsPlayer(Player $player, int $bx, int $by, int $bz): bool
    {
        $half = self::PLAYER_WIDTH / 2;
        $feetY = $player->y - self::PLAYER_EYE_HEIGHT;

        return $player->x + $half > $bx && $player->x - $half < $bx + 1
            && $feetY + self::PLAYER_HEIGHT > $by && $feetY < $by + 1
            && $player->z + $half > $bz && $player->z - $half < $bz + 1;
    }
}
