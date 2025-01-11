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

use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\protocol\LevelEventPacket;
use watermossmc\network\mcpe\protocol\types\ParticleIds;

class ItemBreakParticle implements Particle
{
	public function __construct(private Item $item)
	{
	}

	public function encode(Vector3 $pos) : array
	{
		[$id, $meta] = TypeConverter::getInstance()->getItemTranslator()->toNetworkId($this->item);
		return [LevelEventPacket::standardParticle(ParticleIds::ITEM_BREAK, ($id << 16) | $meta, $pos)];
	}
}
