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

class AnvilDamagePacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ANVIL_DAMAGE_PACKET;

	private BlockPosition $blockPosition;
	private int $damageAmount;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, int $damageAmount) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->damageAmount = $damageAmount;
		return $result;
	}

	public function getDamageAmount() : int
	{
		return $this->damageAmount;
	}

	public function getBlockPosition() : BlockPosition
	{
		return $this->blockPosition;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->damageAmount = $in->getByte();
		$this->blockPosition = $in->getBlockPosition();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->damageAmount);
		$out->putBlockPosition($this->blockPosition);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAnvilDamage($this);
	}
}
