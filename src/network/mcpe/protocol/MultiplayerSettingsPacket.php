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

class MultiplayerSettingsPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MULTIPLAYER_SETTINGS_PACKET;

	public const ACTION_ENABLE_MULTIPLAYER = 0;
	public const ACTION_DISABLE_MULTIPLAYER = 1;
	public const ACTION_REFRESH_JOIN_CODE = 2;

	private int $action;

	/**
	 * @generate-create-func
	 */
	public static function create(int $action) : self
	{
		$result = new self();
		$result->action = $action;
		return $result;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->action = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->action);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMultiplayerSettings($this);
	}
}
