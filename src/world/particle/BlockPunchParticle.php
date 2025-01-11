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

namespace watermossmc\world\particle;

use watermossmc\block\Block;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\protocol\LevelEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelEvent;

/**
 * This particle appears when a player is attacking a block face in survival mode attempting to break it.
 */
class BlockPunchParticle implements Particle
{
	public function __construct(
		private Block $block,
		private int $face
	) {
	}

	public function encode(Vector3 $pos) : array
	{
		return [LevelEventPacket::create(LevelEvent::PARTICLE_PUNCH_BLOCK, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->block->getStateId()) | ($this->face << 24), $pos)];
	}
}
