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

class DisconnectPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::DISCONNECT_PACKET;

	public int $reason; //TODO: add constants / enum
	public ?string $message;
	public ?string $filteredMessage;

	/**
	 * @generate-create-func
	 */
	public static function create(int $reason, ?string $message, ?string $filteredMessage) : self
	{
		$result = new self();
		$result->reason = $reason;
		$result->message = $message;
		$result->filteredMessage = $filteredMessage;
		return $result;
	}

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->reason = $in->getVarInt();
		$skipMessage = $in->getBool();
		$this->message = $skipMessage ? null : $in->getString();
		$this->filteredMessage = $skipMessage ? null : $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->reason);
		$out->putBool($skipMessage = $this->message === null && $this->filteredMessage === null);
		if (!$skipMessage) {
			$out->putString($this->message ?? "");
			$out->putString($this->filteredMessage ?? "");
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleDisconnect($this);
	}
}
