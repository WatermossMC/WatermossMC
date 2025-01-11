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
use watermossmc\network\mcpe\protocol\types\camera\CameraAimAssistCategories;
use watermossmc\network\mcpe\protocol\types\camera\CameraAimAssistPreset;

use function count;

class CameraAimAssistPresetsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_PRESETS_PACKET;

	/** @var CameraAimAssistCategories[] */
	private array $categories;
	/** @var CameraAimAssistPreset[] */
	private array $presets;

	/**
	 * @generate-create-func
	 * @param CameraAimAssistCategories[] $categories
	 * @param CameraAimAssistPreset[]     $presets
	 */
	public static function create(array $categories, array $presets) : self
	{
		$result = new self();
		$result->categories = $categories;
		$result->presets = $presets;
		return $result;
	}

	/**
	 * @return CameraAimAssistCategories[]
	 */
	public function getCategories() : array
	{
		return $this->categories;
	}

	/**
	 * @return CameraAimAssistPreset[]
	 */
	public function getPresets() : array
	{
		return $this->presets;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->categories = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->categories[] = CameraAimAssistCategories::read($in);
		}

		$this->presets = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->presets[] = CameraAimAssistPreset::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->categories));
		foreach ($this->categories as $category) {
			$category->write($out);
		}

		$out->putUnsignedVarInt(count($this->presets));
		foreach ($this->presets as $preset) {
			$preset->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCameraAimAssistPresets($this);
	}
}
