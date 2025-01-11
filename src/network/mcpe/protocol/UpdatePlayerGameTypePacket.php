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
use watermossmc\network\mcpe\protocol\types\GameMode;

class UpdatePlayerGameTypePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_PLAYER_GAME_TYPE_PACKET;

	/** @see GameMode */
	private int $gameMode;
	private int $playerActorUniqueId;
	private int $tick;

	/**
	 * @generate-create-func
	 */
	public static function create(int $gameMode, int $playerActorUniqueId, int $tick) : self
	{
		$result = new self();
		$result->gameMode = $gameMode;
		$result->playerActorUniqueId = $playerActorUniqueId;
		$result->tick = $tick;
		return $result;
	}

	public function getGameMode() : int
	{
		return $this->gameMode;
	}

	public function getPlayerActorUniqueId() : int
	{
		return $this->playerActorUniqueId;
	}

	public function getTick() : int
	{
		return $this->tick;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->gameMode = $in->getVarInt();
		$this->playerActorUniqueId = $in->getActorUniqueId();
		$this->tick = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->gameMode);
		$out->putActorUniqueId($this->playerActorUniqueId);
		$out->putUnsignedVarLong($this->tick);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdatePlayerGameType($this);
	}
}
