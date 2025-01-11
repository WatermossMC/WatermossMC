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

class ScriptMessagePacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SCRIPT_MESSAGE_PACKET;

	private string $messageId;
	private string $value;

	/**
	 * @generate-create-func
	 */
	public static function create(string $messageId, string $value) : self
	{
		$result = new self();
		$result->messageId = $messageId;
		$result->value = $value;
		return $result;
	}

	public function getMessageId() : string
	{
		return $this->messageId;
	}

	public function getValue() : string
	{
		return $this->value;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->messageId = $in->getString();
		$this->value = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->messageId);
		$out->putString($this->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleScriptMessage($this);
	}
}
