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
use watermossmc\network\mcpe\protocol\types\inventory\CreativeContentEntry;

use function count;

class CreativeContentPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CREATIVE_CONTENT_PACKET;

	/** @var CreativeContentEntry[] */
	private array $entries;

	/**
	 * @generate-create-func
	 * @param CreativeContentEntry[] $entries
	 */
	public static function create(array $entries) : self
	{
		$result = new self();
		$result->entries = $entries;
		return $result;
	}

	/** @return CreativeContentEntry[] */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->entries = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->entries[] = CreativeContentEntry::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$entry->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCreativeContent($this);
	}
}
