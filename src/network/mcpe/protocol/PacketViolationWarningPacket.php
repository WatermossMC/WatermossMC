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

class PacketViolationWarningPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PACKET_VIOLATION_WARNING_PACKET;

	public const TYPE_MALFORMED = 0;

	public const SEVERITY_WARNING = 0;
	public const SEVERITY_FINAL_WARNING = 1;
	public const SEVERITY_TERMINATING_CONNECTION = 2;

	private int $type;
	private int $severity;
	private int $packetId;
	private string $message;

	/**
	 * @generate-create-func
	 */
	public static function create(int $type, int $severity, int $packetId, string $message) : self
	{
		$result = new self();
		$result->type = $type;
		$result->severity = $severity;
		$result->packetId = $packetId;
		$result->message = $message;
		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	public function getSeverity() : int
	{
		return $this->severity;
	}

	public function getPacketId() : int
	{
		return $this->packetId;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getVarInt();
		$this->severity = $in->getVarInt();
		$this->packetId = $in->getVarInt();
		$this->message = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->type);
		$out->putVarInt($this->severity);
		$out->putVarInt($this->packetId);
		$out->putString($this->message);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePacketViolationWarning($this);
	}
}
