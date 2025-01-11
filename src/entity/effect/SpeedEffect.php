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

namespace watermossmc\entity\effect;

use watermossmc\entity\Living;

class SpeedEffect extends Effect
{
	public function add(Living $entity, EffectInstance $instance) : void
	{
		$entity->setMovementSpeed($entity->getMovementSpeed() * (1 + 0.2 * $instance->getEffectLevel()));
	}

	public function remove(Living $entity, EffectInstance $instance) : void
	{
		$entity->setMovementSpeed($entity->getMovementSpeed() / (1 + 0.2 * $instance->getEffectLevel()));
	}
}
