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

class MapCreateLockedCopyPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MAP_CREATE_LOCKED_COPY_PACKET;

	public int $originalMapId;
	public int $newMapId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $originalMapId, int $newMapId) : self
	{
		$result = new self();
		$result->originalMapId = $originalMapId;
		$result->newMapId = $newMapId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->originalMapId = $in->getActorUniqueId();
		$this->newMapId = $in->getActorUniqueId();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorUniqueId($this->originalMapId);
		$out->putActorUniqueId($this->newMapId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMapCreateLockedCopy($this);
	}
}
