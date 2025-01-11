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
use watermossmc\network\mcpe\protocol\types\PlayerListEntry;

use function count;

class PlayerListPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;

	public int $type;
	/** @var PlayerListEntry[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param PlayerListEntry[] $entries
	 */
	private static function create(int $type, array $entries) : self
	{
		$result = new self();
		$result->type = $type;
		$result->entries = $entries;
		return $result;
	}

	/**
	 * @param PlayerListEntry[] $entries
	 */
	public static function add(array $entries) : self
	{
		return self::create(self::TYPE_ADD, $entries);
	}

	/**
	 * @param PlayerListEntry[] $entries
	 */
	public static function remove(array $entries) : self
	{
		return self::create(self::TYPE_REMOVE, $entries);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getByte();
		$count = $in->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			$entry = new PlayerListEntry();

			if ($this->type === self::TYPE_ADD) {
				$entry->uuid = $in->getUUID();
				$entry->actorUniqueId = $in->getActorUniqueId();
				$entry->username = $in->getString();
				$entry->xboxUserId = $in->getString();
				$entry->platformChatId = $in->getString();
				$entry->buildPlatform = $in->getLInt();
				$entry->skinData = $in->getSkin();
				$entry->isTeacher = $in->getBool();
				$entry->isHost = $in->getBool();
				$entry->isSubClient = $in->getBool();
			} else {
				$entry->uuid = $in->getUUID();
			}

			$this->entries[$i] = $entry;
		}
		if ($this->type === self::TYPE_ADD) {
			for ($i = 0; $i < $count; ++$i) {
				$this->entries[$i]->skinData->setVerified($in->getBool());
			}
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->type);
		$out->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			if ($this->type === self::TYPE_ADD) {
				$out->putUUID($entry->uuid);
				$out->putActorUniqueId($entry->actorUniqueId);
				$out->putString($entry->username);
				$out->putString($entry->xboxUserId);
				$out->putString($entry->platformChatId);
				$out->putLInt($entry->buildPlatform);
				$out->putSkin($entry->skinData);
				$out->putBool($entry->isTeacher);
				$out->putBool($entry->isHost);
				$out->putBool($entry->isSubClient);
			} else {
				$out->putUUID($entry->uuid);
			}
		}
		if ($this->type === self::TYPE_ADD) {
			foreach ($this->entries as $entry) {
				$out->putBool($entry->skinData->isVerified());
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerList($this);
	}
}
