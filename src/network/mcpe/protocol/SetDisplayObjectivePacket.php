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

class SetDisplayObjectivePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_DISPLAY_OBJECTIVE_PACKET;

	public const DISPLAY_SLOT_LIST = "list";
	public const DISPLAY_SLOT_SIDEBAR = "sidebar";
	public const DISPLAY_SLOT_BELOW_NAME = "belowname";

	public const SORT_ORDER_ASCENDING = 0;
	public const SORT_ORDER_DESCENDING = 1;

	public string $displaySlot;
	public string $objectiveName;
	public string $displayName;
	public string $criteriaName;
	public int $sortOrder;

	/**
	 * @generate-create-func
	 */
	public static function create(string $displaySlot, string $objectiveName, string $displayName, string $criteriaName, int $sortOrder) : self
	{
		$result = new self();
		$result->displaySlot = $displaySlot;
		$result->objectiveName = $objectiveName;
		$result->displayName = $displayName;
		$result->criteriaName = $criteriaName;
		$result->sortOrder = $sortOrder;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->displaySlot = $in->getString();
		$this->objectiveName = $in->getString();
		$this->displayName = $in->getString();
		$this->criteriaName = $in->getString();
		$this->sortOrder = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->displaySlot);
		$out->putString($this->objectiveName);
		$out->putString($this->displayName);
		$out->putString($this->criteriaName);
		$out->putVarInt($this->sortOrder);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetDisplayObjective($this);
	}
}
