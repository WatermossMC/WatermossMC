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

use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\MultiAnySupportTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\World;

use function count;
use function shuffle;

class GlowLichen extends Transparent
{
	use MultiAnySupportTrait;

	public function getLightLevel() : int
	{
		return 7;
	}

	public function isSolid() : bool
	{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	/**
	 * @return int[]
	 */
	protected function getInitialPlaceFaces(Block $blockReplace) : array
	{
		return $blockReplace instanceof GlowLichen ? $blockReplace->faces : [];
	}

	private function getSpreadBlock(Block $replace, int $spreadFace) : ?Block
	{
		if ($replace instanceof self && $replace->hasSameTypeId($this)) {
			if ($replace->hasFace($spreadFace)) {
				return null;
			}
			$result = $replace;
		} elseif ($replace->getTypeId() === BlockTypeIds::AIR) {
			$result = VanillaBlocks::GLOW_LICHEN();
		} else {
			//TODO: if this is a water block, generate a waterlogged block
			return null;
		}
		return $result->setFace($spreadFace, true);
	}

	private function spread(World $world, Vector3 $replacePos, int $spreadFace) : bool
	{
		$supportBlock = $world->getBlock($replacePos->getSide($spreadFace));
		$supportFace = Facing::opposite($spreadFace);

		if ($supportBlock->getSupportType($supportFace) !== SupportType::FULL) {
			return false;
		}

		$replacedBlock = $supportBlock->getSide($supportFace);
		$replacementBlock = $this->getSpreadBlock($replacedBlock, Facing::opposite($supportFace));
		if ($replacementBlock === null) {
			return false;
		}

		return BlockEventHelper::spread($replacedBlock, $replacementBlock, $this);
	}

	/**
	 * @phpstan-return \Generator<int, int, void, void>
	 */
	private static function getShuffledSpreadFaces(int $sourceFace) : \Generator
	{
		$skipAxis = Facing::axis($sourceFace);

		$faces = Facing::ALL;
		shuffle($faces);
		foreach ($faces as $spreadFace) {
			if (Facing::axis($spreadFace) !== $skipAxis) {
				yield $spreadFace;
			}
		}
	}

	private function spreadAroundSupport(int $sourceFace) : bool
	{
		$world = $this->position->getWorld();

		$supportPos = $this->position->getSide($sourceFace);
		foreach (self::getShuffledSpreadFaces($sourceFace) as $spreadFace) {
			$replacePos = $supportPos->getSide($spreadFace);
			if ($this->spread($world, $replacePos, Facing::opposite($spreadFace))) {
				return true;
			}
		}

		return false;
	}

	private function spreadAdjacentToSupport(int $sourceFace) : bool
	{
		$world = $this->position->getWorld();

		foreach (self::getShuffledSpreadFaces($sourceFace) as $spreadFace) {
			$replacePos = $this->position->getSide($spreadFace);
			if ($this->spread($world, $replacePos, $sourceFace)) {
				return true;
			}
		}
		return false;
	}

	private function spreadWithinSelf(int $sourceFace) : bool
	{
		foreach (self::getShuffledSpreadFaces($sourceFace) as $spreadFace) {
			if (!$this->hasFace($spreadFace) && $this->spread($this->position->getWorld(), $this->position, $spreadFace)) {
				return true;
			}
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer && count($this->faces) > 0) {
			$shuffledFaces = $this->faces;
			shuffle($shuffledFaces);

			$spreadMethods = [
				$this->spreadAroundSupport(...),
				$this->spreadAdjacentToSupport(...),
				$this->spreadWithinSelf(...),
			];
			shuffle($spreadMethods);

			foreach ($shuffledFaces as $sourceFace) {
				foreach ($spreadMethods as $spreadMethod) {
					if ($spreadMethod($sourceFace)) {
						$item->pop();
						break 2;
					}
				}
			}

			return true;
		}
		return false;
	}

	public function getDrops(Item $item) : array
	{
		if (($item->getBlockToolType() & BlockToolType::SHEARS) !== 0) {
			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function getFlameEncouragement() : int
	{
		return 15;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
