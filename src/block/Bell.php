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

use watermossmc\block\tile\Bell as TileBell;
use watermossmc\block\utils\BellAttachmentType;
use watermossmc\block\utils\HorizontalFacingTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\projectile\Projectile;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\RayTraceResult;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;
use watermossmc\world\sound\BellRingSound;

final class Bell extends Transparent
{
	use HorizontalFacingTrait;

	private BellAttachmentType $attachmentType = BellAttachmentType::FLOOR;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->attachmentType);
		$w->horizontalFacing($this->facing);
	}

	protected function recalculateCollisionBoxes() : array
	{
		if ($this->attachmentType === BellAttachmentType::FLOOR) {
			return [
				AxisAlignedBB::one()->squash(Facing::axis($this->facing), 1 / 4)->trim(Facing::UP, 3 / 16)
			];
		}
		if ($this->attachmentType === BellAttachmentType::CEILING) {
			return [
				AxisAlignedBB::one()->contract(1 / 4, 0, 1 / 4)->trim(Facing::DOWN, 1 / 4)
			];
		}

		$box = AxisAlignedBB::one()
			->squash(Facing::axis(Facing::rotateY($this->facing, true)), 1 / 4)
			->trim(Facing::UP, 1 / 16)
			->trim(Facing::DOWN, 1 / 4);

		return [
			$this->attachmentType === BellAttachmentType::ONE_WALL ? $box->trim($this->facing, 3 / 16) : $box
		];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function getAttachmentType() : BellAttachmentType
	{
		return $this->attachmentType;
	}

	/** @return $this */
	public function setAttachmentType(BellAttachmentType $attachmentType) : self
	{
		$this->attachmentType = $attachmentType;
		return $this;
	}

	private function canBeSupportedAt(Block $block, int $face) : bool
	{
		return $block->getAdjacentSupportType($face) !== SupportType::NONE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (!$this->canBeSupportedAt($blockReplace, Facing::opposite($face))) {
			return false;
		}
		if ($face === Facing::UP) {
			if ($player !== null) {
				$this->setFacing(Facing::opposite($player->getHorizontalFacing()));
			}
			$this->setAttachmentType(BellAttachmentType::FLOOR);
		} elseif ($face === Facing::DOWN) {
			$this->setAttachmentType(BellAttachmentType::CEILING);
		} else {
			$this->setFacing($face);
			$this->setAttachmentType(
				$this->canBeSupportedAt($blockReplace, $face) ?
					BellAttachmentType::TWO_WALLS :
					BellAttachmentType::ONE_WALL
			);
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void
	{
		foreach (match($this->attachmentType) {
			BellAttachmentType::CEILING => [Facing::UP],
			BellAttachmentType::FLOOR => [Facing::DOWN],
			BellAttachmentType::ONE_WALL => [Facing::opposite($this->facing)],
			BellAttachmentType::TWO_WALLS => [$this->facing, Facing::opposite($this->facing)]
		} as $supportBlockDirection) {
			if (!$this->canBeSupportedAt($this, $supportBlockDirection)) {
				$this->position->getWorld()->useBreakOn($this->position);
				break;
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player !== null) {
			$faceHit = Facing::opposite($player->getHorizontalFacing());
			if ($this->isValidFaceToRing($faceHit)) {
				$this->ring($faceHit);
				return true;
			}
		}

		return false;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		$faceHit = Facing::opposite($projectile->getHorizontalFacing());
		if ($this->isValidFaceToRing($faceHit)) {
			$this->ring($faceHit);
		}
	}

	public function ring(int $faceHit) : void
	{
		$world = $this->position->getWorld();
		$world->addSound($this->position, new BellRingSound());
		$tile = $world->getTile($this->position);
		if ($tile instanceof TileBell) {
			$world->broadcastPacketToViewers($this->position, $tile->createFakeUpdatePacket($faceHit));
		}
	}

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		return [$this->asItem()];
	}

	private function isValidFaceToRing(int $faceHit) : bool
	{
		return match($this->attachmentType) {
			BellAttachmentType::CEILING => true,
			BellAttachmentType::FLOOR => Facing::axis($faceHit) === Facing::axis($this->facing),
			BellAttachmentType::ONE_WALL, BellAttachmentType::TWO_WALLS => $faceHit === Facing::rotateY($this->facing, false) || $faceHit === Facing::rotateY($this->facing, true),
		};
	}
}
