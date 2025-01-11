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

namespace watermossmc\entity\object;

use watermossmc\block\VanillaBlocks;
use watermossmc\entity\Entity;
use watermossmc\entity\EntitySizeInfo;
use watermossmc\entity\Explosive;
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\event\entity\EntityPreExplodeEvent;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\network\mcpe\protocol\types\entity\EntityIds;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use watermossmc\world\Explosion;
use watermossmc\world\Position;

class PrimedTNT extends Entity implements Explosive
{
	private const TAG_FUSE = "Fuse"; //TAG_Short

	public static function getNetworkTypeId() : string
	{
		return EntityIds::TNT;
	}

	protected int $fuse;
	protected bool $worksUnderwater = false;

	protected function getInitialSizeInfo() : EntitySizeInfo
	{
		return new EntitySizeInfo(0.98, 0.98);
	}

	protected function getInitialDragMultiplier() : float
	{
		return 0.02;
	}

	protected function getInitialGravity() : float
	{
		return 0.04;
	}

	public function getFuse() : int
	{
		return $this->fuse;
	}

	public function setFuse(int $fuse) : void
	{
		if ($fuse < 0 || $fuse > 32767) {
			throw new \InvalidArgumentException("Fuse must be in the range 0-32767");
		}
		$this->fuse = $fuse;
		$this->networkPropertiesDirty = true;
	}

	public function worksUnderwater() : bool
	{
		return $this->worksUnderwater;
	}

	public function setWorksUnderwater(bool $worksUnderwater) : void
	{
		$this->worksUnderwater = $worksUnderwater;
		$this->networkPropertiesDirty = true;
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source->getCause() === EntityDamageEvent::CAUSE_VOID) {
			parent::attack($source);
		}
	}

	protected function initEntity(CompoundTag $nbt) : void
	{
		parent::initEntity($nbt);

		$this->fuse = $nbt->getShort(self::TAG_FUSE, 80);
	}

	public function canCollideWith(Entity $entity) : bool
	{
		return false;
	}

	public function saveNBT() : CompoundTag
	{
		$nbt = parent::saveNBT();
		$nbt->setShort(self::TAG_FUSE, $this->fuse);

		return $nbt;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if (!$this->isFlaggedForDespawn()) {
			$this->fuse -= $tickDiff;
			$this->networkPropertiesDirty = true;

			if ($this->fuse <= 0) {
				$this->flagForDespawn();
				$this->explode();
			}
		}

		return $hasUpdate || $this->fuse >= 0;
	}

	public function explode() : void
	{
		$ev = new EntityPreExplodeEvent($this, 4);
		$ev->call();
		if (!$ev->isCancelled()) {
			//TODO: deal with underwater TNT (underwater TNT treats water as if it has a blast resistance of 0)
			$explosion = new Explosion(Position::fromObject($this->location->add(0, $this->size->getHeight() / 2, 0), $this->getWorld()), $ev->getRadius(), $this);
			if ($ev->isBlockBreaking()) {
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	public function getPickedItem() : ?Item
	{
		return VanillaBlocks::TNT()->setWorksUnderwater($this->worksUnderwater)->asItem();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void
	{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::IGNITED, true);
		$properties->setInt(EntityMetadataProperties::VARIANT, $this->worksUnderwater ? 1 : 0);
		$properties->setInt(EntityMetadataProperties::FUSE_LENGTH, $this->fuse);
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3
	{
		return $vector3->add(0, 0.49, 0);
	}
}
