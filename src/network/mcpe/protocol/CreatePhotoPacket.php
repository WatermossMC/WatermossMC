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

class CreatePhotoPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CREATE_PHOTO_PACKET;

	private int $actorUniqueId;
	private string $photoName;
	private string $photoItemName;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorUniqueId, string $photoName, string $photoItemName) : self
	{
		$result = new self();
		$result->actorUniqueId = $actorUniqueId;
		$result->photoName = $photoName;
		$result->photoItemName = $photoItemName;
		return $result;
	}

	public function getActorUniqueId() : int
	{
		return $this->actorUniqueId;
	}

	public function getPhotoName() : string
	{
		return $this->photoName;
	}

	public function getPhotoItemName() : string
	{
		return $this->photoItemName;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorUniqueId = $in->getLLong(); //why be consistent mojang ?????
		$this->photoName = $in->getString();
		$this->photoItemName = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLLong($this->actorUniqueId);
		$out->putString($this->photoName);
		$out->putString($this->photoItemName);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCreatePhoto($this);
	}
}
