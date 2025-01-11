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

class StopSoundPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::STOP_SOUND_PACKET;

	public string $soundName;
	public bool $stopAll;
	public bool $stopLegacyMusic;

	/**
	 * @generate-create-func
	 */
	public static function create(string $soundName, bool $stopAll, bool $stopLegacyMusic) : self
	{
		$result = new self();
		$result->soundName = $soundName;
		$result->stopAll = $stopAll;
		$result->stopLegacyMusic = $stopLegacyMusic;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->soundName = $in->getString();
		$this->stopAll = $in->getBool();
		$this->stopLegacyMusic = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->soundName);
		$out->putBool($this->stopAll);
		$out->putBool($this->stopLegacyMusic);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleStopSound($this);
	}
}
