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

use watermossmc\block\tile\Cauldron as TileCauldron;
use watermossmc\entity\Entity;
use watermossmc\event\entity\EntityCombustByBlockEvent;
use watermossmc\event\entity\EntityDamageByBlockEvent;
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\item\VanillaItems;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\CauldronEmptyLavaSound;
use watermossmc\world\sound\CauldronFillLavaSound;
use watermossmc\world\sound\Sound;

use function assert;

final class LavaCauldron extends FillableCauldron
{
	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileCauldron);

		$tile->setCustomWaterColor(null);
		$tile->setPotionItem(null);
	}

	public function getLightLevel() : int
	{
		return 15;
	}

	public function getFillSound() : Sound
	{
		return new CauldronFillLavaSound();
	}

	public function getEmptySound() : Sound
	{
		return new CauldronEmptyLavaSound();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		match($item->getTypeId()) {
			ItemTypeIds::BUCKET => $this->removeFillLevels(self::MAX_FILL_LEVEL, $item, VanillaItems::LAVA_BUCKET(), $returnedItems),
			ItemTypeIds::POWDER_SNOW_BUCKET, ItemTypeIds::WATER_BUCKET => $this->mix($item, VanillaItems::BUCKET(), $returnedItems),
			ItemTypeIds::LINGERING_POTION, ItemTypeIds::POTION, ItemTypeIds::SPLASH_POTION => $this->mix($item, VanillaItems::GLASS_BOTTLE(), $returnedItems),
			default => null
		};
		return true;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		$ev->call();
		if (!$ev->isCancelled()) {
			$entity->setOnFire($ev->getDuration());
		}

		return true;
	}
}
