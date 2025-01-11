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

class CodeBuilderSourcePacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CODE_BUILDER_SOURCE_PACKET;

	private int $operation;
	private int $category;
	private int $codeStatus;

	/**
	 * @generate-create-func
	 */
	public static function create(int $operation, int $category, int $codeStatus) : self
	{
		$result = new self();
		$result->operation = $operation;
		$result->category = $category;
		$result->codeStatus = $codeStatus;
		return $result;
	}

	public function getOperation() : int
	{
		return $this->operation;
	}

	public function getCategory() : int
	{
		return $this->category;
	}

	public function getCodeStatus() : int
	{
		return $this->codeStatus;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->operation = $in->getByte();
		$this->category = $in->getByte();
		$this->codeStatus = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->operation);
		$out->putByte($this->category);
		$out->putByte($this->codeStatus);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCodeBuilderSource($this);
	}
}
