<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\block;

use watermossmc\block\utils\AgeableTrait;
use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\FortuneDropHelper;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\entity\Entity;
use watermossmc\entity\Living;
use watermossmc\event\entity\EntityDamageByBlockEvent;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\SweetBerriesPickSound;

use function mt_rand;

class SweetBerryBush extends Flowable
{
	use AgeableTrait;
	use StaticSupportTrait;

	public const STAGE_SAPLING = 0;
	public const STAGE_BUSH_NO_BERRIES = 1;
	public const STAGE_BUSH_SOME_BERRIES = 2;
	public const STAGE_MATURE = 3;
	public const MAX_AGE = self::STAGE_MATURE;

	public function getBerryDropAmount() : int
	{
		if ($this->age === self::STAGE_MATURE) {
			return mt_rand(2, 3);
		} elseif ($this->age >= self::STAGE_BUSH_SOME_BERRIES) {
			return mt_rand(1, 2);
		}
		return 0;
	}

	/**
	 * @deprecated
	 */
	protected function canBeSupportedBy(Block $block) : bool
	{
		return $block->getTypeId() !== BlockTypeIds::FARMLAND && //bedrock-specific thing (bug?)
			($block->hasTypeTag(BlockTypeTags::DIRT) || $block->hasTypeTag(BlockTypeTags::MUD));
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::DOWN);
		return $this->canBeSupportedBy($supportBlock);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$world = $this->position->getWorld();
		if ($this->age < self::STAGE_MATURE && $item instanceof Fertilizer) {
			$block = clone $this;
			$block->age++;
			if (BlockEventHelper::grow($this, $block, $player)) {
				$item->pop();
			}
		} elseif (($dropAmount = $this->getBerryDropAmount()) > 0) {
			$world->setBlock($this->position, $this->setAge(self::STAGE_BUSH_NO_BERRIES));
			$world->dropItem($this->position, $this->asItem()->setCount($dropAmount));
			$world->addSound($this->position, new SweetBerriesPickSound());
		}

		return true;
	}

	public function asItem() : Item
	{
		return VanillaItems::SWEET_BERRIES();
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$count = match($this->age) {
			self::STAGE_MATURE => FortuneDropHelper::discrete($item, 2, 3),
			self::STAGE_BUSH_SOME_BERRIES => FortuneDropHelper::discrete($item, 1, 2),
			default => 0
		};
		return [
			$this->asItem()->setCount($count)
		];
	}

	public function ticksRandomly() : bool
	{
		return $this->age < self::STAGE_MATURE;
	}

	public function onRandomTick() : void
	{
		if ($this->age < self::STAGE_MATURE && mt_rand(0, 2) === 1) {
			$block = clone $this;
			++$block->age;
			BlockEventHelper::grow($this, $block, null);
		}
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		if ($this->age >= self::STAGE_BUSH_NO_BERRIES && $entity instanceof Living) {
			$entity->resetFallDistance();

			//TODO: in MCPE, this only triggers if moving while inside the bush block - we don't have the system to deal
			//with that reliably right now
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageByBlockEvent::CAUSE_CONTACT, 1));
		}
		return true;
	}
}
