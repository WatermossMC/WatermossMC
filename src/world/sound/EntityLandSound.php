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

namespace watermossmc\world\sound;

use watermossmc\block\Block;
use watermossmc\entity\Entity;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\protocol\LevelSoundEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelSoundEvent;

/**
 * Played when an entity hits the ground after falling a distance that doesn't cause damage, e.g. due to jumping.
 */
class EntityLandSound implements Sound
{
	public function __construct(
		private Entity $entity,
		private Block $blockLandedOn
	) {
	}

	public function encode(Vector3 $pos) : array
	{
		return [LevelSoundEventPacket::create(
			LevelSoundEvent::LAND,
			$pos,
			TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->blockLandedOn->getStateId()),
			$this->entity::getNetworkTypeId(),
			false, //TODO: does isBaby have any relevance here?
			false
		)];
	}
}
