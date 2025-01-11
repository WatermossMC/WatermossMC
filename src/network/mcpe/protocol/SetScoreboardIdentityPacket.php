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
use watermossmc\network\mcpe\protocol\types\ScoreboardIdentityPacketEntry;

use function count;

class SetScoreboardIdentityPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_SCOREBOARD_IDENTITY_PACKET;

	public const TYPE_REGISTER_IDENTITY = 0;
	public const TYPE_CLEAR_IDENTITY = 1;

	public int $type;
	/** @var ScoreboardIdentityPacketEntry[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param ScoreboardIdentityPacketEntry[] $entries
	 */
	public static function create(int $type, array $entries) : self
	{
		$result = new self();
		$result->type = $type;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getByte();
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$entry = new ScoreboardIdentityPacketEntry();
			$entry->scoreboardId = $in->getVarLong();
			if ($this->type === self::TYPE_REGISTER_IDENTITY) {
				$entry->actorUniqueId = $in->getActorUniqueId();
			}

			$this->entries[] = $entry;
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->type);
		$out->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$out->putVarLong($entry->scoreboardId);
			if ($this->type === self::TYPE_REGISTER_IDENTITY) {
				$out->putActorUniqueId($entry->actorUniqueId);
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetScoreboardIdentity($this);
	}
}
