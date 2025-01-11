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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\UpdateBlockPacket;

final class UpdateSubChunkBlocksPacketEntry
{
	public function __construct(
		private BlockPosition $blockPosition,
		private int $blockRuntimeId,
		private int $flags,
		//These two fields are useless 99.9% of the time; they are here to allow this packet to provide UpdateBlockSyncedPacket functionality.
		private int $syncedUpdateActorUniqueId,
		private int $syncedUpdateType
	) {
	}

	public static function simple(BlockPosition $blockPosition, int $blockRuntimeId) : self
	{
		return new self($blockPosition, $blockRuntimeId, UpdateBlockPacket::FLAG_NETWORK, 0, 0);
	}

	public function getBlockPosition() : BlockPosition
	{
		return $this->blockPosition;
	}

	public function getBlockRuntimeId() : int
	{
		return $this->blockRuntimeId;
	}

	public function getFlags() : int
	{
		return $this->flags;
	}

	public function getSyncedUpdateActorUniqueId() : int
	{
		return $this->syncedUpdateActorUniqueId;
	}

	public function getSyncedUpdateType() : int
	{
		return $this->syncedUpdateType;
	}

	public static function read(PacketSerializer $in) : self
	{
		$blockPosition = $in->getBlockPosition();
		$blockRuntimeId = $in->getUnsignedVarInt();
		$updateFlags = $in->getUnsignedVarInt();
		$syncedUpdateActorUniqueId = $in->getUnsignedVarLong(); //this can't use the standard method because it's unsigned as opposed to the usual signed... !!!!!!
		$syncedUpdateType = $in->getUnsignedVarInt(); //this isn't even consistent with UpdateBlockSyncedPacket?!

		return new self($blockPosition, $blockRuntimeId, $updateFlags, $syncedUpdateActorUniqueId, $syncedUpdateType);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putBlockPosition($this->blockPosition);
		$out->putUnsignedVarInt($this->blockRuntimeId);
		$out->putUnsignedVarInt($this->flags);
		$out->putUnsignedVarLong($this->syncedUpdateActorUniqueId);
		$out->putUnsignedVarInt($this->syncedUpdateType);
	}
}
