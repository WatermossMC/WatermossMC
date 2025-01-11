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
use watermossmc\network\mcpe\protocol\types\entity\UpdateAttribute;

use function count;

class UpdateAttributesPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_ATTRIBUTES_PACKET;

	public int $actorRuntimeId;
	/** @var UpdateAttribute[] */
	public array $entries = [];
	public int $tick = 0;

	/**
	 * @generate-create-func
	 * @param UpdateAttribute[] $entries
	 */
	public static function create(int $actorRuntimeId, array $entries, int $tick) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->entries = $entries;
		$result->tick = $tick;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->entries[] = UpdateAttribute::read($in);
		}
		$this->tick = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$entry->write($out);
		}
		$out->putUnsignedVarLong($this->tick);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateAttributes($this);
	}
}
