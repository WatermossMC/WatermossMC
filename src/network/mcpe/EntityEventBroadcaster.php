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

use watermossmc\entity\Attribute;
use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\Entity;
use watermossmc\entity\Human;
use watermossmc\entity\Living;
use watermossmc\network\mcpe\protocol\types\entity\MetadataProperty;

/**
 * This class allows broadcasting entity events to many viewers on the server network.
 */
interface EntityEventBroadcaster
{
	/**
	 * @param NetworkSession[] $recipients
	 * @param Attribute[]      $attributes
	 */
	public function syncAttributes(array $recipients, Living $entity, array $attributes) : void;

	/**
	 * @param NetworkSession[]   $recipients
	 * @param MetadataProperty[] $properties
	 *
	 * @phpstan-param array<int, MetadataProperty> $properties
	 */
	public function syncActorData(array $recipients, Entity $entity, array $properties) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEntityEffectAdded(array $recipients, Living $entity, EffectInstance $effect, bool $replacesOldEffect) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEntityEffectRemoved(array $recipients, Living $entity, EffectInstance $effect) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEntityRemoved(array $recipients, Entity $entity) : void;

	/**
	 * TODO: expand this to more than just humans
	 *
	 * @param NetworkSession[] $recipients
	 */
	public function onMobMainHandItemChange(array $recipients, Human $mob) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onMobOffHandItemChange(array $recipients, Human $mob) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onMobArmorChange(array $recipients, Living $mob) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onPickUpItem(array $recipients, Entity $collector, Entity $pickedUp) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEmote(array $recipients, Human $from, string $emoteId) : void;
}
