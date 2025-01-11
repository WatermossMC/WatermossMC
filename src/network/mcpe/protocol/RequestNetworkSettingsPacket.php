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

/**
 * This is the first packet sent in a game session. It contains the client's protocol version.
 * The server is expected to respond to this with network settings, which will instruct the client which compression
 * type to use, amongst other things.
 */
class RequestNetworkSettingsPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::REQUEST_NETWORK_SETTINGS_PACKET;

	private int $protocolVersion;

	/**
	 * @generate-create-func
	 */
	public static function create(int $protocolVersion) : self
	{
		$result = new self();
		$result->protocolVersion = $protocolVersion;
		return $result;
	}

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	public function getProtocolVersion() : int
	{
		return $this->protocolVersion;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->protocolVersion = $in->getInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putInt($this->protocolVersion);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleRequestNetworkSettings($this);
	}
}
