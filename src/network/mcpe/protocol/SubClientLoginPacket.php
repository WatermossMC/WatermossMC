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

class SubClientLoginPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SUB_CLIENT_LOGIN_PACKET;

	public string $connectionRequestData;

	/**
	 * @generate-create-func
	 */
	public static function create(string $connectionRequestData) : self
	{
		$result = new self();
		$result->connectionRequestData = $connectionRequestData;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->connectionRequestData = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->connectionRequestData);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSubClientLogin($this);
	}
}
