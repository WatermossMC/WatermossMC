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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function is_bool;
use function is_float;

/**
 * Sent by the client to request server enabling/disabling/changing certain abilities, such as flying, noclip, etc.
 * As of 1.19.0, the vanilla server only handles this for flying/noclip, despite there being a large range of additional
 * abilities which could be requested, and the packet supporting the use of float values.
 */
class RequestAbilityPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::REQUEST_ABILITY_PACKET;

	private const VALUE_TYPE_BOOL = 1;
	private const VALUE_TYPE_FLOAT = 2;

	public const ABILITY_FLYING = 9;
	public const ABILITY_NOCLIP = 17;

	private int $abilityId;
	private float|bool $abilityValue;

	/**
	 * @generate-create-func
	 */
	public static function create(int $abilityId, float|bool $abilityValue) : self
	{
		$result = new self();
		$result->abilityId = $abilityId;
		$result->abilityValue = $abilityValue;
		return $result;
	}

	public function getAbilityId() : int
	{
		return $this->abilityId;
	}

	public function getAbilityValue() : float|bool
	{
		return $this->abilityValue;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->abilityId = $in->getVarInt();

		$valueType = $in->getByte();

		//what is the point of having a type ID if you just write all the types anyway ??? mojang ...
		//only one of these values is ever used; the other(s) are discarded
		$boolValue = $in->getBool();
		$floatValue = $in->getLFloat();

		$this->abilityValue = match($valueType) {
			self::VALUE_TYPE_BOOL => $boolValue,
			self::VALUE_TYPE_FLOAT => $floatValue,
			default => throw new PacketDecodeException("Unknown ability value type $valueType")
		};
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->abilityId);

		[$valueType, $boolValue, $floatValue] = match(true) {
			is_bool($this->abilityValue) => [self::VALUE_TYPE_BOOL, $this->abilityValue, 0.0],
			is_float($this->abilityValue) => [self::VALUE_TYPE_FLOAT, false, $this->abilityValue],
			default => throw new \LogicException("Unreachable")
		};
		$out->putByte($valueType);
		$out->putBool($boolValue);
		$out->putLFloat($floatValue);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleRequestAbility($this);
	}
}
