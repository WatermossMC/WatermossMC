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

class OnScreenTextureAnimationPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ON_SCREEN_TEXTURE_ANIMATION_PACKET;

	public int $effectId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $effectId) : self
	{
		$result = new self();
		$result->effectId = $effectId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->effectId = $in->getLInt(); //unsigned
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLInt($this->effectId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleOnScreenTextureAnimation($this);
	}
}
