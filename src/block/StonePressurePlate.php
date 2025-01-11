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

namespace watermossmc\block;

use watermossmc\entity\Entity;
use watermossmc\entity\Living;

use function array_filter;

class StonePressurePlate extends SimplePressurePlate
{
	protected function filterIrrelevantEntities(array $entities) : array
	{
		return array_filter($entities, fn (Entity $e) => $e instanceof Living); //TODO: armor stands should activate stone plates too
	}
}
