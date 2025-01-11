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

class UpdateClientInputLocksPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_CLIENT_INPUT_LOCKS_PACKET;

	private int $flags;
	private Vector3 $position;

	/**
	 * @generate-create-func
	 */
	public static function create(int $flags, Vector3 $position) : self
	{
		$result = new self();
		$result->flags = $flags;
		$result->position = $position;
		return $result;
	}

	public function getFlags() : int
	{
		return $this->flags;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->flags = $in->getUnsignedVarInt();
		$this->position = $in->getVector3();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->flags);
		$out->putVector3($this->position);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateClientInputLocks($this);
	}
}
