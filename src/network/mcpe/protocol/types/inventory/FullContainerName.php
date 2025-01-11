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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class FullContainerName
{
	public function __construct(
		private int $containerId,
		private ?int $dynamicId = null
	) {
	}

	public function getContainerId() : int
	{
		return $this->containerId;
	}

	public function getDynamicId() : ?int
	{
		return $this->dynamicId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$containerId = $in->getByte();
		$dynamicId = $in->readOptional($in->getLInt(...));
		return new self($containerId, $dynamicId);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->containerId);
		$out->writeOptional($this->dynamicId, $out->putLInt(...));
	}
}
