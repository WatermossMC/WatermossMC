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
use watermossmc\network\mcpe\protocol\types\hud\HudElement;
use watermossmc\network\mcpe\protocol\types\hud\HudVisibility;

use function count;

class SetHudPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_HUD_PACKET;

	/** @var HudElement[] */
	private array $hudElements = [];
	private HudVisibility $visibility;

	/**
	 * @generate-create-func
	 * @param HudElement[] $hudElements
	 */
	public static function create(array $hudElements, HudVisibility $visibility) : self
	{
		$result = new self();
		$result->hudElements = $hudElements;
		$result->visibility = $visibility;
		return $result;
	}

	/** @return HudElement[] */
	public function getHudElements() : array
	{
		return $this->hudElements;
	}

	public function getVisibility() : HudVisibility
	{
		return $this->visibility;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->hudElements = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->hudElements[] = HudElement::fromPacket($in->getByte());
		}
		$this->visibility = HudVisibility::fromPacket($in->getByte());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->hudElements));
		foreach ($this->hudElements as $element) {
			$out->putByte($element->value);
		}
		$out->putByte($this->visibility->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetHud($this);
	}
}
