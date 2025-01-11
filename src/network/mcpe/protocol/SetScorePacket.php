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
use watermossmc\network\mcpe\protocol\types\ScorePacketEntry;

use function count;

class SetScorePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	public const TYPE_CHANGE = 0;
	public const TYPE_REMOVE = 1;

	public int $type;
	/** @var ScorePacketEntry[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param ScorePacketEntry[] $entries
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
		for ($i = 0, $i2 = $in->getUnsignedVarInt(); $i < $i2; ++$i) {
			$entry = new ScorePacketEntry();
			$entry->scoreboardId = $in->getVarLong();
			$entry->objectiveName = $in->getString();
			$entry->score = $in->getLInt();
			if ($this->type !== self::TYPE_REMOVE) {
				$entry->type = $in->getByte();
				switch ($entry->type) {
					case ScorePacketEntry::TYPE_PLAYER:
					case ScorePacketEntry::TYPE_ENTITY:
						$entry->actorUniqueId = $in->getActorUniqueId();
						break;
					case ScorePacketEntry::TYPE_FAKE_PLAYER:
						$entry->customName = $in->getString();
						break;
					default:
						throw new PacketDecodeException("Unknown entry type $entry->type");
				}
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
			$out->putString($entry->objectiveName);
			$out->putLInt($entry->score);
			if ($this->type !== self::TYPE_REMOVE) {
				$out->putByte($entry->type);
				switch ($entry->type) {
					case ScorePacketEntry::TYPE_PLAYER:
					case ScorePacketEntry::TYPE_ENTITY:
						$out->putActorUniqueId($entry->actorUniqueId);
						break;
					case ScorePacketEntry::TYPE_FAKE_PLAYER:
						$out->putString($entry->customName);
						break;
					default:
						throw new \InvalidArgumentException("Unknown entry type $entry->type");
				}
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetScore($this);
	}
}
