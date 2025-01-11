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

class UpdateBlockSyncedPacket extends UpdateBlockPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_SYNCED_PACKET;

	public const TYPE_NONE = 0;
	public const TYPE_CREATE = 1;
	public const TYPE_DESTROY = 2;

	public int $actorUniqueId;
	public int $updateType;

	protected function decodePayload(PacketSerializer $in) : void
	{
		parent::decodePayload($in);
		$this->actorUniqueId = $in->getUnsignedVarLong();
		$this->updateType = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		parent::encodePayload($out);
		$out->putUnsignedVarLong($this->actorUniqueId);
		$out->putUnsignedVarLong($this->updateType);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateBlockSynced($this);
	}
}
