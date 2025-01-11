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

class SetDifficultyPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_DIFFICULTY_PACKET;

	public int $difficulty;

	/**
	 * @generate-create-func
	 */
	public static function create(int $difficulty) : self
	{
		$result = new self();
		$result->difficulty = $difficulty;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->difficulty = $in->getUnsignedVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->difficulty);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetDifficulty($this);
	}
}
