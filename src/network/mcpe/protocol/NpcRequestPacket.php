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

class NpcRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::NPC_REQUEST_PACKET;

	public const REQUEST_SET_ACTIONS = 0;
	public const REQUEST_EXECUTE_ACTION = 1;
	public const REQUEST_EXECUTE_CLOSING_COMMANDS = 2;
	public const REQUEST_SET_NAME = 3;
	public const REQUEST_SET_SKIN = 4;
	public const REQUEST_SET_INTERACTION_TEXT = 5;
	public const REQUEST_EXECUTE_OPENING_COMMANDS = 6;

	public int $actorRuntimeId;
	public int $requestType;
	public string $commandString;
	public int $actionIndex;
	public string $sceneName;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, int $requestType, string $commandString, int $actionIndex, string $sceneName) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->requestType = $requestType;
		$result->commandString = $commandString;
		$result->actionIndex = $actionIndex;
		$result->sceneName = $sceneName;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->requestType = $in->getByte();
		$this->commandString = $in->getString();
		$this->actionIndex = $in->getByte();
		$this->sceneName = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putByte($this->requestType);
		$out->putString($this->commandString);
		$out->putByte($this->actionIndex);
		$out->putString($this->sceneName);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleNpcRequest($this);
	}
}
