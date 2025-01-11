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
use watermossmc\network\mcpe\protocol\types\ServerAuthMovementMode;

class SetMovementAuthorityPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_MOVEMENT_AUTHORITY_PACKET;

	private ServerAuthMovementMode $mode;

	/**
	 * @generate-create-func
	 */
	public static function create(ServerAuthMovementMode $mode) : self
	{
		$result = new self();
		$result->mode = $mode;
		return $result;
	}

	public function getMode() : ServerAuthMovementMode
	{
		return $this->mode;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->mode = ServerAuthMovementMode::fromPacket($in->getByte());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->mode->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetMovementAuthority($this);
	}
}
