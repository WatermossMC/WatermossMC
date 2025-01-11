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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class TrimMaterial
{
	public function __construct(
		private string $materialId,
		private string $color,
		private string $itemId
	) {
	}

	public function getMaterialId() : string
	{
		return $this->materialId;
	}

	public function getColor() : string
	{
		return $this->color;
	}

	public function getItemId() : string
	{
		return $this->itemId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$materialId = $in->getString();
		$color = $in->getString();
		$itemId = $in->getString();
		return new self($materialId, $color, $itemId);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->materialId);
		$out->putString($this->color);
		$out->putString($this->itemId);
	}
}
