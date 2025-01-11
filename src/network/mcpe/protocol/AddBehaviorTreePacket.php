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

class AddBehaviorTreePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_BEHAVIOR_TREE_PACKET;

	public string $behaviorTreeJson;

	/**
	 * @generate-create-func
	 */
	public static function create(string $behaviorTreeJson) : self
	{
		$result = new self();
		$result->behaviorTreeJson = $behaviorTreeJson;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->behaviorTreeJson = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->behaviorTreeJson);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAddBehaviorTree($this);
	}
}
