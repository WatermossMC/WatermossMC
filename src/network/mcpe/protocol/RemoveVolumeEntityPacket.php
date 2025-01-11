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

class RemoveVolumeEntityPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::REMOVE_VOLUME_ENTITY_PACKET;

	private int $entityNetId;
	private int $dimension;

	/**
	 * @generate-create-func
	 */
	public static function create(int $entityNetId, int $dimension) : self
	{
		$result = new self();
		$result->entityNetId = $entityNetId;
		$result->dimension = $dimension;
		return $result;
	}

	public function getEntityNetId() : int
	{
		return $this->entityNetId;
	}

	public function getDimension() : int
	{
		return $this->dimension;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->entityNetId = $in->getUnsignedVarInt();
		$this->dimension = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->entityNetId);
		$out->putVarInt($this->dimension);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleRemoveVolumeEntity($this);
	}
}
