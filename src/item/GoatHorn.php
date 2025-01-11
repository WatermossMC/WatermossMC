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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\GoatHornSound;

class GoatHorn extends Item implements Releasable
{
	private GoatHornType $goatHornType = GoatHornType::PONDER;

	protected function describeState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->goatHornType);
	}

	public function getHornType() : GoatHornType
	{
		return $this->goatHornType;
	}

	/**
	 * @return $this
	 */
	public function setHornType(GoatHornType $type) : self
	{
		$this->goatHornType = $type;
		return $this;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getCooldownTicks() : int
	{
		return 140;
	}

	public function getCooldownTag() : ?string
	{
		return ItemCooldownTags::GOAT_HORN;
	}

	public function canStartUsingItem(Player $player) : bool
	{
		return true;
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult
	{
		$position = $player->getPosition();
		$position->getWorld()->addSound($position, new GoatHornSound($this->goatHornType));

		return ItemUseResult::SUCCESS;
	}
}
