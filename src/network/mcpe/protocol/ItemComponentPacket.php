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
use watermossmc\network\mcpe\protocol\types\CacheableNbt;
use watermossmc\network\mcpe\protocol\types\ItemComponentPacketEntry;

use function count;

class ItemComponentPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ITEM_COMPONENT_PACKET;

	/**
	 * @var ItemComponentPacketEntry[]
	 * @phpstan-var list<ItemComponentPacketEntry>
	 */
	private array $entries;

	/**
	 * @generate-create-func
	 * @param ItemComponentPacketEntry[] $entries
	 * @phpstan-param list<ItemComponentPacketEntry> $entries
	 */
	public static function create(array $entries) : self
	{
		$result = new self();
		$result->entries = $entries;
		return $result;
	}

	/**
	 * @return ItemComponentPacketEntry[]
	 * @phpstan-return list<ItemComponentPacketEntry>
	 */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->entries = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$name = $in->getString();
			$nbt = $in->getNbtCompoundRoot();
			$this->entries[] = new ItemComponentPacketEntry($name, new CacheableNbt($nbt));
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$out->putString($entry->getName());
			$out->put($entry->getComponentNbt()->getEncodedNbt());
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleItemComponent($this);
	}
}
