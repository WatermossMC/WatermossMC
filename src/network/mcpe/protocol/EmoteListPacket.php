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

use Ramsey\Uuid\UuidInterface;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

class EmoteListPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::EMOTE_LIST_PACKET;

	private int $playerActorRuntimeId;
	/** @var UuidInterface[] */
	private array $emoteIds;

	/**
	 * @generate-create-func
	 * @param UuidInterface[] $emoteIds
	 */
	public static function create(int $playerActorRuntimeId, array $emoteIds) : self
	{
		$result = new self();
		$result->playerActorRuntimeId = $playerActorRuntimeId;
		$result->emoteIds = $emoteIds;
		return $result;
	}

	public function getPlayerActorRuntimeId() : int
	{
		return $this->playerActorRuntimeId;
	}

	/** @return UuidInterface[] */
	public function getEmoteIds() : array
	{
		return $this->emoteIds;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->playerActorRuntimeId = $in->getActorRuntimeId();
		$this->emoteIds = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->emoteIds[] = $in->getUUID();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->playerActorRuntimeId);
		$out->putUnsignedVarInt(count($this->emoteIds));
		foreach ($this->emoteIds as $emoteId) {
			$out->putUUID($emoteId);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleEmoteList($this);
	}
}
