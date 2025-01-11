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

class ServerToClientHandshakePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVER_TO_CLIENT_HANDSHAKE_PACKET;

	/** Server pubkey and token is contained in the JWT. */
	public string $jwt;

	/**
	 * @generate-create-func
	 */
	public static function create(string $jwt) : self
	{
		$result = new self();
		$result->jwt = $jwt;
		return $result;
	}

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->jwt = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->jwt);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleServerToClientHandshake($this);
	}
}
