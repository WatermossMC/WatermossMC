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

namespace watermossmc\item;

use watermossmc\entity\Entity;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class NameTag extends Item
{
	public function onInteractEntity(Player $player, Entity $entity, Vector3 $clickVector) : bool
	{
		if ($entity->canBeRenamed() && $this->hasCustomName()) {
			$entity->setNameTag($this->getCustomName());
			$this->pop();
			return true;
		}
		return false;
	}
}
