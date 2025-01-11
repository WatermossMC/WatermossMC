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

class ShowProfilePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SHOW_PROFILE_PACKET;

	public string $xuid;

	/**
	 * @generate-create-func
	 */
	public static function create(string $xuid) : self
	{
		$result = new self();
		$result->xuid = $xuid;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->xuid = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->xuid);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleShowProfile($this);
	}
}
