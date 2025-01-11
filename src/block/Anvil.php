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

use watermossmc\block\inventory\AnvilInventory;
use watermossmc\block\utils\Fallable;
use watermossmc\block\utils\FallableTrait;
use watermossmc\block\utils\HorizontalFacingTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\object\FallingBlock;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\utils\Utils;
use watermossmc\world\BlockTransaction;
use watermossmc\world\sound\AnvilFallSound;
use watermossmc\world\sound\Sound;

use function round;

class Anvil extends Transparent implements Fallable
{
	use FallableTrait;
	use HorizontalFacingTrait;

	public const UNDAMAGED = 0;
	public const SLIGHTLY_DAMAGED = 1;
	public const VERY_DAMAGED = 2;

	private int $damage = self::UNDAMAGED;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(self::UNDAMAGED, self::VERY_DAMAGED, $this->damage);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
	}

	public function getDamage() : int
	{
		return $this->damage;
	}

	/** @return $this */
	public function setDamage(int $damage) : self
	{
		if ($damage < self::UNDAMAGED || $damage > self::VERY_DAMAGED) {
			throw new \InvalidArgumentException("Damage must be in range " . self::UNDAMAGED . " ... " . self::VERY_DAMAGED);
		}
		$this->damage = $damage;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [AxisAlignedBB::one()->squash(Facing::axis(Facing::rotateY($this->facing, false)), 1 / 8)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player instanceof Player) {
			$player->setCurrentWindow(new AnvilInventory($this->position));
		}

		return true;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($player !== null) {
			$this->facing = Facing::rotateY($player->getHorizontalFacing(), false);
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onHitGround(FallingBlock $blockEntity) : bool
	{
		if (Utils::getRandomFloat() < 0.05 + (round($blockEntity->getFallDistance()) - 1) * 0.05) {
			if ($this->damage !== self::VERY_DAMAGED) {
				$this->damage = $this->damage + 1;
			} else {
				return false;
			}
		}
		return true;
	}

	public function getFallDamagePerBlock() : float
	{
		return 2.0;
	}

	public function getMaxFallDamage() : float
	{
		return 40.0;
	}

	public function getLandSound() : ?Sound
	{
		return new AnvilFallSound();
	}
}
