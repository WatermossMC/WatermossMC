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

class Boat extends Item
{
	private BoatType $boatType;

	public function __construct(ItemIdentifier $identifier, string $name, BoatType $boatType)
	{
		parent::__construct($identifier, $name);
		$this->boatType = $boatType;
	}

	public function getType() : BoatType
	{
		return $this->boatType;
	}

	public function getFuelTime() : int
	{
		return 1200; //400 in PC
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	//TODO
}
