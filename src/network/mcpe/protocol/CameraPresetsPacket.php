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
use watermossmc\network\mcpe\protocol\types\camera\CameraPreset;

use function count;

class CameraPresetsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_PRESETS_PACKET;

	/** @var CameraPreset[] */
	private array $presets;

	/**
	 * @generate-create-func
	 * @param CameraPreset[] $presets
	 */
	public static function create(array $presets) : self
	{
		$result = new self();
		$result->presets = $presets;
		return $result;
	}

	/**
	 * @return CameraPreset[]
	 */
	public function getPresets() : array
	{
		return $this->presets;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->presets = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; $i++) {
			$this->presets[] = CameraPreset::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->presets));
		foreach ($this->presets as $preset) {
			$preset->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCameraPresets($this);
	}
}
