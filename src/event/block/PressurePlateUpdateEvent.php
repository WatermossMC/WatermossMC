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

namespace watermossmc\event\block;

use watermossmc\block\Block;
use watermossmc\entity\Entity;

/**
 * Called whenever the list of entities on a pressure plate changes.
 * Depending on the type of pressure plate, this might turn on/off its signal, or change the signal strength.
 */
final class PressurePlateUpdateEvent extends BaseBlockChangeEvent
{
	/**
	 * @param Entity[] $activatingEntities
	 */
	public function __construct(
		Block $block,
		Block $newState,
		private array $activatingEntities
	) {
		parent::__construct($block, $newState);
	}

	/**
	 * Returns a list of entities intersecting the pressure plate's activation box.
	 * If the pressure plate is about to deactivate, this list will be empty.
	 *
	 * @return Entity[]
	 */
	public function getActivatingEntities() : array
	{
		return $this->activatingEntities;
	}
}
