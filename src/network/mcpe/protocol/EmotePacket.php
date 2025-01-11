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

class EmotePacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::EMOTE_PACKET;

	public const FLAG_SERVER = 1 << 0;
	public const FLAG_MUTE_ANNOUNCEMENT = 1 << 1;

	private int $actorRuntimeId;
	private string $emoteId;
	private int $emoteLengthTicks;
	private string $xboxUserId;
	private string $platformChatId;
	private int $flags;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, string $emoteId, int $emoteLengthTicks, string $xboxUserId, string $platformChatId, int $flags) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->emoteId = $emoteId;
		$result->emoteLengthTicks = $emoteLengthTicks;
		$result->xboxUserId = $xboxUserId;
		$result->platformChatId = $platformChatId;
		$result->flags = $flags;
		return $result;
	}

	public function getActorRuntimeId() : int
	{
		return $this->actorRuntimeId;
	}

	public function getEmoteId() : string
	{
		return $this->emoteId;
	}

	public function getEmoteLengthTicks() : int
	{
		return $this->emoteLengthTicks;
	}

	public function getXboxUserId() : string
	{
		return $this->xboxUserId;
	}

	public function getPlatformChatId() : string
	{
		return $this->platformChatId;
	}

	public function getFlags() : int
	{
		return $this->flags;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->emoteId = $in->getString();
		$this->emoteLengthTicks = $in->getUnsignedVarInt();
		$this->xboxUserId = $in->getString();
		$this->platformChatId = $in->getString();
		$this->flags = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putString($this->emoteId);
		$out->putUnsignedVarInt($this->emoteLengthTicks);
		$out->putString($this->xboxUserId);
		$out->putString($this->platformChatId);
		$out->putByte($this->flags);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleEmote($this);
	}
}
