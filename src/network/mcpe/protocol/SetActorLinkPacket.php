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
use watermossmc\network\mcpe\protocol\types\entity\EntityLink;

class SetActorLinkPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_ACTOR_LINK_PACKET;

	public EntityLink $link;

	/**
	 * @generate-create-func
	 */
	public static function create(EntityLink $link) : self
	{
		$result = new self();
		$result->link = $link;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->link = $in->getEntityLink();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putEntityLink($this->link);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetActorLink($this);
	}
}
