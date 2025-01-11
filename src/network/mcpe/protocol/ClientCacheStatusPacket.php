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

class ClientCacheStatusPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_STATUS_PACKET;

	private bool $enabled;

	/**
	 * @generate-create-func
	 */
	public static function create(bool $enabled) : self
	{
		$result = new self();
		$result->enabled = $enabled;
		return $result;
	}

	public function isEnabled() : bool
	{
		return $this->enabled;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->enabled = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->enabled);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleClientCacheStatus($this);
	}
}
