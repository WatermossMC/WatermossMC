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

use watermossmc\block\tile\Barrel as TileBarrel;
use watermossmc\block\utils\AnyFacingTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function abs;

class Barrel extends Opaque
{
	use AnyFacingTrait;

	protected bool $open = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->facing($this->facing);
		$w->bool($this->open);
	}

	public function isOpen() : bool
	{
		return $this->open;
	}

	/** @return $this */
	public function setOpen(bool $open) : Barrel
	{
		$this->open = $open;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($player !== null) {
			if (abs($player->getPosition()->x - $this->position->x) < 2 && abs($player->getPosition()->z - $this->position->z) < 2) {
				$y = $player->getEyePos()->y;

				if ($y - $this->position->y > 2) {
					$this->facing = Facing::UP;
				} elseif ($this->position->y - $y > 0) {
					$this->facing = Facing::DOWN;
				} else {
					$this->facing = Facing::opposite($player->getHorizontalFacing());
				}
			} else {
				$this->facing = Facing::opposite($player->getHorizontalFacing());
			}
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player instanceof Player) {
			$barrel = $this->position->getWorld()->getTile($this->position);
			if ($barrel instanceof TileBarrel) {
				if (!$barrel->canOpenWith($item->getCustomName())) {
					return true;
				}

				$player->setCurrentWindow($barrel->getInventory());
			}
		}

		return true;
	}

	public function getFuelTime() : int
	{
		return 300;
	}
}
