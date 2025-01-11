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
use watermossmc\network\mcpe\protocol\types\BlockPosition;

/**
 * Sent by the server to open the sign GUI for a sign.
 */
class OpenSignPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::OPEN_SIGN_PACKET;

	private BlockPosition $blockPosition;
	private bool $front;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, bool $front) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->front = $front;
		return $result;
	}

	public function getBlockPosition() : BlockPosition
	{
		return $this->blockPosition;
	}

	public function isFront() : bool
	{
		return $this->front;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->blockPosition = $in->getBlockPosition();
		$this->front = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBlockPosition($this->blockPosition);
		$out->putBool($this->front);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleOpenSign($this);
	}
}
