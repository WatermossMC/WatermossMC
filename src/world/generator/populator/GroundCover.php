<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

namespace watermossmc\world\generator\populator;

use watermossmc\block\BlockTypeIds;
use watermossmc\block\Liquid;
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\utils\Random;
use watermossmc\world\biome\BiomeRegistry;
use watermossmc\world\ChunkManager;
use watermossmc\world\format\Chunk;

use function count;
use function min;

class GroundCover implements Populator
{
	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void
	{
		$chunk = $world->getChunk($chunkX, $chunkZ) ?? throw new \InvalidArgumentException("Chunk $chunkX $chunkZ does not yet exist");
		$factory = RuntimeBlockStateRegistry::getInstance();
		$biomeRegistry = BiomeRegistry::getInstance();
		for ($x = 0; $x < Chunk::EDGE_LENGTH; ++$x) {
			for ($z = 0; $z < Chunk::EDGE_LENGTH; ++$z) {
				$biome = $biomeRegistry->getBiome($chunk->getBiomeId($x, 0, $z));
				$cover = $biome->getGroundCover();
				if (count($cover) > 0) {
					$diffY = 0;
					if (!$cover[0]->isSolid()) {
						$diffY = 1;
					}

					$startY = 127;
					for (; $startY > 0; --$startY) {
						if (!$factory->fromStateId($chunk->getBlockStateId($x, $startY, $z))->isTransparent()) {
							break;
						}
					}
					$startY = min(127, $startY + $diffY);
					$endY = $startY - count($cover);
					for ($y = $startY; $y > $endY && $y >= 0; --$y) {
						$b = $cover[$startY - $y];
						$id = $factory->fromStateId($chunk->getBlockStateId($x, $y, $z));
						if ($id->getTypeId() === BlockTypeIds::AIR && $b->isSolid()) {
							break;
						}
						if ($b->canBeFlowedInto() && $id instanceof Liquid) {
							continue;
						}

						$chunk->setBlockStateId($x, $y, $z, $b->getStateId());
					}
				}
			}
		}
	}
}
