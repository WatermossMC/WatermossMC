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

class ClientboundCloseFormPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_CLOSE_FORM_PACKET;

	/**
	 * @generate-create-func
	 */
	public static function create() : self
	{
		return new self();
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		//No payload
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		//No payload
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleClientboundCloseForm($this);
	}
}
