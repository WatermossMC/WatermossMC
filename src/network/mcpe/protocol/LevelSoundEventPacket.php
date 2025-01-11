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

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\LevelSoundEvent;

class LevelSoundEventPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::LEVEL_SOUND_EVENT_PACKET;

	/** @see LevelSoundEvent */
	public int $sound;
	public Vector3 $position;
	public int $extraData = -1;
	public string $entityType = ":"; //???
	public bool $isBabyMob = false; //...
	public bool $disableRelativeVolume = false;

	/**
	 * @generate-create-func
	 */
	public static function create(int $sound, Vector3 $position, int $extraData, string $entityType, bool $isBabyMob, bool $disableRelativeVolume) : self
	{
		$result = new self();
		$result->sound = $sound;
		$result->position = $position;
		$result->extraData = $extraData;
		$result->entityType = $entityType;
		$result->isBabyMob = $isBabyMob;
		$result->disableRelativeVolume = $disableRelativeVolume;
		return $result;
	}

	public static function nonActorSound(int $sound, Vector3 $position, bool $disableRelativeVolume, int $extraData = -1) : self
	{
		return self::create($sound, $position, $extraData, ":", false, $disableRelativeVolume);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->sound = $in->getUnsignedVarInt();
		$this->position = $in->getVector3();
		$this->extraData = $in->getVarInt();
		$this->entityType = $in->getString();
		$this->isBabyMob = $in->getBool();
		$this->disableRelativeVolume = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->sound);
		$out->putVector3($this->position);
		$out->putVarInt($this->extraData);
		$out->putString($this->entityType);
		$out->putBool($this->isBabyMob);
		$out->putBool($this->disableRelativeVolume);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleLevelSoundEvent($this);
	}
}
