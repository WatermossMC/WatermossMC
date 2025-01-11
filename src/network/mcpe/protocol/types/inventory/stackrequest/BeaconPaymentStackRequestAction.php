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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackrequest;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Completes a transaction involving a beacon consuming input to produce effects.
 */
final class BeaconPaymentStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::BEACON_PAYMENT;

	public function __construct(
		private int $primaryEffectId,
		private int $secondaryEffectId
	) {
	}

	public function getPrimaryEffectId() : int
	{
		return $this->primaryEffectId;
	}

	public function getSecondaryEffectId() : int
	{
		return $this->secondaryEffectId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$primary = $in->getVarInt();
		$secondary = $in->getVarInt();
		return new self($primary, $secondary);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarInt($this->primaryEffectId);
		$out->putVarInt($this->secondaryEffectId);
	}
}
