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
use watermossmc\event\entity\EntityCombustByBlockEvent;
use watermossmc\event\entity\EntityDamageByBlockEvent;
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\math\Facing;
use watermossmc\world\sound\BucketEmptyLavaSound;
use watermossmc\world\sound\BucketFillLavaSound;
use watermossmc\world\sound\Sound;

class Lava extends Liquid
{
	public function getLightLevel() : int
	{
		return 15;
	}

	public function getBucketFillSound() : Sound
	{
		return new BucketFillLavaSound();
	}

	public function getBucketEmptySound() : Sound
	{
		return new BucketEmptyLavaSound();
	}

	public function tickRate() : int
	{
		return 30;
	}

	public function getFlowDecayPerBlock() : int
	{
		return 2; //TODO: this is 1 in the nether
	}

	/**
	 * @phpstan-return \Generator<int, Block, void, void>
	 */
	private function getAdjacentBlocksExceptDown() : \Generator
	{
		foreach (Facing::ALL as $side) {
			if ($side === Facing::DOWN) {
				continue;
			}
			yield $this->getSide($side);
		}
	}

	protected function checkForHarden() : bool
	{
		if ($this->falling) {
			return false;
		}
		foreach ($this->getAdjacentBlocksExceptDown() as $colliding) {
			if ($colliding instanceof Water) {
				if ($this->decay === 0) {
					$this->liquidCollide($colliding, VanillaBlocks::OBSIDIAN());
					return true;
				} elseif ($this->decay <= 4) {
					$this->liquidCollide($colliding, VanillaBlocks::COBBLESTONE());
					return true;
				}
			}
		}

		if ($this->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::SOUL_SOIL) {
			foreach ($this->getAdjacentBlocksExceptDown() as $colliding) {
				if ($colliding->getTypeId() === BlockTypeIds::BLUE_ICE) {
					$this->liquidCollide($colliding, VanillaBlocks::BASALT());
					return true;
				}
			}
		}

		return false;
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay, bool $falling) : void
	{
		if ($block instanceof Water) {
			$block->liquidCollide($this, VanillaBlocks::STONE());
		} else {
			parent::flowIntoBlock($block, $newFlowDecay, $falling);
		}
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
		$entity->attack($ev);

		//in java burns entities for 15 seconds - seems to be a parity issue in bedrock
		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		$ev->call();
		if (!$ev->isCancelled()) {
			$entity->setOnFire($ev->getDuration());
		}

		$entity->resetFallDistance();
		return true;
	}
}
