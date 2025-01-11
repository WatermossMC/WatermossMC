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

class SimpleEventPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SIMPLE_EVENT_PACKET;

	public const TYPE_ENABLE_COMMANDS = 1;
	public const TYPE_DISABLE_COMMANDS = 2;
	public const TYPE_UNLOCK_WORLD_TEMPLATE_SETTINGS = 3;

	public int $eventType;

	/**
	 * @generate-create-func
	 */
	public static function create(int $eventType) : self
	{
		$result = new self();
		$result->eventType = $eventType;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->eventType = $in->getLShort();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLShort($this->eventType);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSimpleEvent($this);
	}
}
