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

class NpcDialoguePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::NPC_DIALOGUE_PACKET;

	public const ACTION_OPEN = 0;
	public const ACTION_CLOSE = 1;

	private int $npcActorUniqueId;
	private int $actionType;
	private string $dialogue;
	private string $sceneName;
	private string $npcName;
	private string $actionJson;

	/**
	 * @generate-create-func
	 */
	public static function create(int $npcActorUniqueId, int $actionType, string $dialogue, string $sceneName, string $npcName, string $actionJson) : self
	{
		$result = new self();
		$result->npcActorUniqueId = $npcActorUniqueId;
		$result->actionType = $actionType;
		$result->dialogue = $dialogue;
		$result->sceneName = $sceneName;
		$result->npcName = $npcName;
		$result->actionJson = $actionJson;
		return $result;
	}

	public function getNpcActorUniqueId() : int
	{
		return $this->npcActorUniqueId;
	}

	public function getActionType() : int
	{
		return $this->actionType;
	}

	public function getDialogue() : string
	{
		return $this->dialogue;
	}

	public function getSceneName() : string
	{
		return $this->sceneName;
	}

	public function getNpcName() : string
	{
		return $this->npcName;
	}

	public function getActionJson() : string
	{
		return $this->actionJson;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->npcActorUniqueId = $in->getLLong(); //WHY NOT USING STANDARD METHODS, MOJANG
		$this->actionType = $in->getVarInt();
		$this->dialogue = $in->getString();
		$this->sceneName = $in->getString();
		$this->npcName = $in->getString();
		$this->actionJson = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLLong($this->npcActorUniqueId);
		$out->putVarInt($this->actionType);
		$out->putString($this->dialogue);
		$out->putString($this->sceneName);
		$out->putString($this->npcName);
		$out->putString($this->actionJson);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleNpcDialogue($this);
	}
}
