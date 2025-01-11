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

namespace watermossmc\network\mcpe;

use watermossmc\data\bedrock\EffectIdMap;
use watermossmc\entity\Attribute;
use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\Entity;
use watermossmc\entity\Human;
use watermossmc\entity\Living;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\protocol\ClientboundPacket;
use watermossmc\network\mcpe\protocol\EmotePacket;
use watermossmc\network\mcpe\protocol\MobArmorEquipmentPacket;
use watermossmc\network\mcpe\protocol\MobEffectPacket;
use watermossmc\network\mcpe\protocol\MobEquipmentPacket;
use watermossmc\network\mcpe\protocol\RemoveActorPacket;
use watermossmc\network\mcpe\protocol\SetActorDataPacket;
use watermossmc\network\mcpe\protocol\TakeItemActorPacket;
use watermossmc\network\mcpe\protocol\types\entity\PropertySyncData;
use watermossmc\network\mcpe\protocol\types\entity\UpdateAttribute;
use watermossmc\network\mcpe\protocol\types\inventory\ContainerIds;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStack;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use watermossmc\network\mcpe\protocol\UpdateAttributesPacket;

use function array_map;
use function count;
use function ksort;

use const SORT_NUMERIC;

final class StandardEntityEventBroadcaster implements EntityEventBroadcaster
{
	public function __construct(
		private PacketBroadcaster $broadcaster,
		private TypeConverter $typeConverter
	) {
	}

	/**
	 * @param NetworkSession[] $recipients
	 */
	private function sendDataPacket(array $recipients, ClientboundPacket $packet) : void
	{
		$this->broadcaster->broadcastPackets($recipients, [$packet]);
	}

	public function syncAttributes(array $recipients, Living $entity, array $attributes) : void
	{
		if (count($attributes) > 0) {
			$this->sendDataPacket($recipients, UpdateAttributesPacket::create(
				$entity->getId(),
				array_map(fn (Attribute $attr) => new UpdateAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getDefaultValue(), []), $attributes),
				0
			));
		}
	}

	public function syncActorData(array $recipients, Entity $entity, array $properties) : void
	{
		//TODO: HACK! as of 1.18.10, the client responds differently to the same data ordered in different orders - for
		//example, sending HEIGHT in the list before FLAGS when unsetting the SWIMMING flag results in a hitbox glitch
		ksort($properties, SORT_NUMERIC);
		$this->sendDataPacket($recipients, SetActorDataPacket::create($entity->getId(), $properties, new PropertySyncData([], []), 0));
	}

	public function onEntityEffectAdded(array $recipients, Living $entity, EffectInstance $effect, bool $replacesOldEffect) : void
	{
		//TODO: we may need yet another effect <=> ID map in the future depending on protocol changes
		$this->sendDataPacket($recipients, MobEffectPacket::add(
			$entity->getId(),
			$replacesOldEffect,
			EffectIdMap::getInstance()->toId($effect->getType()),
			$effect->getAmplifier(),
			$effect->isVisible(),
			$effect->getDuration(),
			tick: 0
		));
	}

	public function onEntityEffectRemoved(array $recipients, Living $entity, EffectInstance $effect) : void
	{
		$this->sendDataPacket($recipients, MobEffectPacket::remove($entity->getId(), EffectIdMap::getInstance()->toId($effect->getType()), tick: 0));
	}

	public function onEntityRemoved(array $recipients, Entity $entity) : void
	{
		$this->sendDataPacket($recipients, RemoveActorPacket::create($entity->getId()));
	}

	public function onMobMainHandItemChange(array $recipients, Human $mob) : void
	{
		//TODO: we could send zero for slot here because remote players don't need to know which slot was selected
		$inv = $mob->getInventory();
		$this->sendDataPacket($recipients, MobEquipmentPacket::create(
			$mob->getId(),
			ItemStackWrapper::legacy($this->typeConverter->coreItemStackToNet($inv->getItemInHand())),
			$inv->getHeldItemIndex(),
			$inv->getHeldItemIndex(),
			ContainerIds::INVENTORY
		));
	}

	public function onMobOffHandItemChange(array $recipients, Human $mob) : void
	{
		$inv = $mob->getOffHandInventory();
		$this->sendDataPacket($recipients, MobEquipmentPacket::create(
			$mob->getId(),
			ItemStackWrapper::legacy($this->typeConverter->coreItemStackToNet($inv->getItem(0))),
			0,
			0,
			ContainerIds::OFFHAND
		));
	}

	public function onMobArmorChange(array $recipients, Living $mob) : void
	{
		$inv = $mob->getArmorInventory();
		$converter = $this->typeConverter;
		$this->sendDataPacket($recipients, MobArmorEquipmentPacket::create(
			$mob->getId(),
			ItemStackWrapper::legacy($converter->coreItemStackToNet($inv->getHelmet())),
			ItemStackWrapper::legacy($converter->coreItemStackToNet($inv->getChestplate())),
			ItemStackWrapper::legacy($converter->coreItemStackToNet($inv->getLeggings())),
			ItemStackWrapper::legacy($converter->coreItemStackToNet($inv->getBoots())),
			new ItemStackWrapper(0, ItemStack::null())
		));
	}

	public function onPickUpItem(array $recipients, Entity $collector, Entity $pickedUp) : void
	{
		$this->sendDataPacket($recipients, TakeItemActorPacket::create($collector->getId(), $pickedUp->getId()));
	}

	public function onEmote(array $recipients, Human $from, string $emoteId) : void
	{
		$this->sendDataPacket($recipients, EmotePacket::create(
			$from->getId(),
			$emoteId,
			0, //seems to be irrelevant for the client, we cannot risk rebroadcasting random values received
			"",
			"",
			EmotePacket::FLAG_SERVER | EmotePacket::FLAG_MUTE_ANNOUNCEMENT
		));
	}
}
