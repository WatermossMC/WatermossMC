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

use watermossmc\block\utils\WoodType;
use watermossmc\block\utils\WoodTypeTrait;

class WoodenPressurePlate extends SimplePressurePlate
{
	use WoodTypeTrait;

	public function __construct(
		BlockIdentifier $idInfo,
		string $name,
		BlockTypeInfo $typeInfo,
		WoodType $woodType,
		int $deactivationDelayTicks = 20 //TODO: make this mandatory in PM6
	) {
		$this->woodType = $woodType;
		parent::__construct($idInfo, $name, $typeInfo, $deactivationDelayTicks);
	}

	public function getFuelTime() : int
	{
		return 300;
	}
}
