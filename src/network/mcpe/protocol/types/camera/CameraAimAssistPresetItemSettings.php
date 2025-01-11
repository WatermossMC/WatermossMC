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

namespace watermossmc\network\mcpe\protocol\types\camera;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class CameraAimAssistPresetItemSettings
{
	public function __construct(
		private string $itemIdentifier,
		private string $categoryName,
	) {
	}

	public function getItemIdentifier() : string
	{
		return $this->itemIdentifier;
	}

	public function getCategoryName() : string
	{
		return $this->categoryName;
	}

	public static function read(PacketSerializer $in) : self
	{
		$itemIdentifier = $in->getString();
		$categoryName = $in->getString();
		return new self(
			$itemIdentifier,
			$categoryName
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->itemIdentifier);
		$out->putString($this->categoryName);
	}
}
