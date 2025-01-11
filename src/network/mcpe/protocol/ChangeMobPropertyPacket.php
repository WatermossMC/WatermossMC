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

/**
 * This appears to be some kind of debug packet. Does nothing in release mode.
 * I have no words for the structure of this packet ...
 */
class ChangeMobPropertyPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CHANGE_MOB_PROPERTY_PACKET;

	private int $actorUniqueId;
	private string $propertyName;
	private bool $boolValue;
	private string $stringValue;
	private int $intValue;
	private float $floatValue;

	/**
	 * @generate-create-func
	 */
	private static function create(int $actorUniqueId, string $propertyName, bool $boolValue, string $stringValue, int $intValue, float $floatValue) : self
	{
		$result = new self();
		$result->actorUniqueId = $actorUniqueId;
		$result->propertyName = $propertyName;
		$result->boolValue = $boolValue;
		$result->stringValue = $stringValue;
		$result->intValue = $intValue;
		$result->floatValue = $floatValue;
		return $result;
	}

	public static function boolValue(int $actorUniqueId, string $propertyName, bool $value) : self
	{
		return self::create($actorUniqueId, $propertyName, $value, "", 0, 0);
	}

	public static function stringValue(int $actorUniqueId, string $propertyName, string $value) : self
	{
		return self::create($actorUniqueId, $propertyName, false, $value, 0, 0);
	}

	public static function intValue(int $actorUniqueId, string $propertyName, int $value) : self
	{
		return self::create($actorUniqueId, $propertyName, false, "", $value, 0);
	}

	public static function floatValue(int $actorUniqueId, string $propertyName, float $value) : self
	{
		return self::create($actorUniqueId, $propertyName, false, "", 0, $value);
	}

	public function getActorUniqueId() : int
	{
		return $this->actorUniqueId;
	}

	public function getPropertyName() : string
	{
		return $this->propertyName;
	}

	public function isBoolValue() : bool
	{
		return $this->boolValue;
	}

	public function getStringValue() : string
	{
		return $this->stringValue;
	}

	public function getIntValue() : int
	{
		return $this->intValue;
	}

	public function getFloatValue() : float
	{
		return $this->floatValue;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorUniqueId = $in->getActorUniqueId();
		$this->propertyName = $in->getString();
		$this->boolValue = $in->getBool();
		$this->stringValue = $in->getString();
		$this->intValue = $in->getVarInt();
		$this->floatValue = $in->getLFloat();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorUniqueId($this->actorUniqueId);
		$out->putString($this->propertyName);
		$out->putBool($this->boolValue);
		$out->putString($this->stringValue);
		$out->putVarInt($this->intValue);
		$out->putLFloat($this->floatValue);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleChangeMobProperty($this);
	}
}
