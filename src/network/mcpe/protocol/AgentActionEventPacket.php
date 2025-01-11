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
use watermossmc\network\mcpe\protocol\types\AgentActionType;

/**
 * Used by code builder, exact purpose unclear
 */
class AgentActionEventPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::AGENT_ACTION_EVENT_PACKET;

	private string $requestId;
	/** @see AgentActionType */
	private int $action;
	private string $responseJson;

	/**
	 * @generate-create-func
	 */
	public static function create(string $requestId, int $action, string $responseJson) : self
	{
		$result = new self();
		$result->requestId = $requestId;
		$result->action = $action;
		$result->responseJson = $responseJson;
		return $result;
	}

	public function getRequestId() : string
	{
		return $this->requestId;
	}

	/** @see AgentActionType */
	public function getAction() : int
	{
		return $this->action;
	}

	public function getResponseJson() : string
	{
		return $this->responseJson;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->requestId = $in->getString();
		$this->action = $in->getLInt();
		$this->responseJson = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->requestId);
		$out->putLInt($this->action);
		$out->putString($this->responseJson);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAgentActionEvent($this);
	}
}
