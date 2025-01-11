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

use watermossmc\block\tile\Furnace as TileFurnace;
use watermossmc\block\utils\FacesOppositePlacingPlayerTrait;
use watermossmc\block\utils\LightableTrait;
use watermossmc\crafting\FurnaceType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function mt_rand;

class Furnace extends Opaque
{
	use FacesOppositePlacingPlayerTrait;
	use LightableTrait;

	protected FurnaceType $furnaceType;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, FurnaceType $furnaceType)
	{
		$this->furnaceType = $furnaceType;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->lit);
	}

	public function getFurnaceType() : FurnaceType
	{
		return $this->furnaceType;
	}

	public function getLightLevel() : int
	{
		return $this->lit ? 13 : 0;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player instanceof Player) {
			$furnace = $this->position->getWorld()->getTile($this->position);
			if ($furnace instanceof TileFurnace && $furnace->canOpenWith($item->getCustomName())) {
				$player->setCurrentWindow($furnace->getInventory());
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void
	{
		$world = $this->position->getWorld();
		$furnace = $world->getTile($this->position);
		if ($furnace instanceof TileFurnace && $furnace->onUpdate()) {
			if (mt_rand(1, 60) === 1) { //in vanilla this is between 1 and 5 seconds; try to average about 3
				$world->addSound($this->position, $furnace->getFurnaceType()->getCookSound());
			}
			$world->scheduleDelayedBlockUpdate($this->position, 1); //TODO: check this
		}
	}
}
