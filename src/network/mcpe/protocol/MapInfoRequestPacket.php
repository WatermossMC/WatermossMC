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
use watermossmc\network\mcpe\protocol\types\MapImage;
use watermossmc\network\mcpe\protocol\types\MapInfoRequestPacketClientPixel;

use function count;

class MapInfoRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MAP_INFO_REQUEST_PACKET;

	public int $mapId;
	/** @var MapInfoRequestPacketClientPixel[] */
	public array $clientPixels = [];

	/**
	 * @generate-create-func
	 * @param MapInfoRequestPacketClientPixel[] $clientPixels
	 */
	public static function create(int $mapId, array $clientPixels) : self
	{
		$result = new self();
		$result->mapId = $mapId;
		$result->clientPixels = $clientPixels;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->mapId = $in->getActorUniqueId();

		$this->clientPixels = [];
		$count = $in->getLInt();
		if ($count > MapImage::MAX_HEIGHT * MapImage::MAX_WIDTH) {
			throw new PacketDecodeException("Too many pixels");
		}
		for ($i = 0; $i < $count; $i++) {
			$this->clientPixels[] = MapInfoRequestPacketClientPixel::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorUniqueId($this->mapId);

		$out->putLInt(count($this->clientPixels));
		foreach ($this->clientPixels as $pixel) {
			$pixel->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMapInfoRequest($this);
	}
}
