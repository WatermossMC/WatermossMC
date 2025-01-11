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
use watermossmc\network\mcpe\protocol\types\BlockPosition;

class ContainerOpenPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_OPEN_PACKET;

	public int $windowId;
	public int $windowType;
	public BlockPosition $blockPosition;
	public int $actorUniqueId = -1;

	/**
	 * @generate-create-func
	 */
	private static function create(int $windowId, int $windowType, BlockPosition $blockPosition, int $actorUniqueId) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->windowType = $windowType;
		$result->blockPosition = $blockPosition;
		$result->actorUniqueId = $actorUniqueId;
		return $result;
	}

	public static function blockInv(int $windowId, int $windowType, BlockPosition $blockPosition) : self
	{
		return self::create($windowId, $windowType, $blockPosition, -1);
	}

	public static function entityInv(int $windowId, int $windowType, int $actorUniqueId) : self
	{
		return self::create($windowId, $windowType, new BlockPosition(0, 0, 0), $actorUniqueId);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->windowId = $in->getByte();
		$this->windowType = $in->getByte();
		$this->blockPosition = $in->getBlockPosition();
		$this->actorUniqueId = $in->getActorUniqueId();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->windowId);
		$out->putByte($this->windowType);
		$out->putBlockPosition($this->blockPosition);
		$out->putActorUniqueId($this->actorUniqueId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleContainerOpen($this);
	}
}
