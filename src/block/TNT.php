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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\Location;
use watermossmc\entity\object\PrimedTNT;
use watermossmc\entity\projectile\Projectile;
use watermossmc\item\Durable;
use watermossmc\item\enchantment\VanillaEnchantments;
use watermossmc\item\FlintSteel;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\math\RayTraceResult;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\utils\Random;
use watermossmc\world\sound\IgniteSound;

use function cos;
use function sin;

use const M_PI;

class TNT extends Opaque
{
	protected bool $unstable = false; //TODO: Usage unclear, seems to be a weird hack in vanilla
	protected bool $worksUnderwater = false;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->worksUnderwater);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->unstable);
	}

	public function isUnstable() : bool
	{
		return $this->unstable;
	}

	/** @return $this */
	public function setUnstable(bool $unstable) : self
	{
		$this->unstable = $unstable;
		return $this;
	}

	public function worksUnderwater() : bool
	{
		return $this->worksUnderwater;
	}

	/** @return $this */
	public function setWorksUnderwater(bool $worksUnderwater) : self
	{
		$this->worksUnderwater = $worksUnderwater;
		return $this;
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->unstable) {
			$this->ignite();
			return true;
		}
		return parent::onBreak($item, $player, $returnedItems);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item->getTypeId() === ItemTypeIds::FIRE_CHARGE) {
			$item->pop();
			$this->ignite();
			return true;
		}
		if ($item instanceof FlintSteel || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())) {
			if ($item instanceof Durable) {
				$item->applyDamage(1);
			}
			$this->ignite();
			return true;
		}

		return false;
	}

	public function ignite(int $fuse = 80) : void
	{
		$world = $this->position->getWorld();
		$world->setBlock($this->position, VanillaBlocks::AIR());

		$mot = (new Random())->nextSignedFloat() * M_PI * 2;

		$tnt = new PrimedTNT(Location::fromObject($this->position->add(0.5, 0, 0.5), $world));
		$tnt->setFuse($fuse);
		$tnt->setWorksUnderwater($this->worksUnderwater);
		$tnt->setMotion(new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));

		$tnt->spawnToAll();
		$tnt->broadcastSound(new IgniteSound());
	}

	public function getFlameEncouragement() : int
	{
		return 15;
	}

	public function getFlammability() : int
	{
		return 100;
	}

	public function onIncinerate() : void
	{
		$this->ignite();
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		if ($projectile->isOnFire()) {
			$this->ignite();
		}
	}
}
