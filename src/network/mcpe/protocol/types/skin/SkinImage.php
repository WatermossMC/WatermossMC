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

namespace watermossmc\network\mcpe\protocol\types\skin;

use function strlen;

class SkinImage
{
	public function __construct(
		private int $height,
		private int $width,
		private string $data
	) {
		if ($height < 0 || $width < 0) {
			throw new \InvalidArgumentException("Height and width cannot be negative");
		}
		if (($expected = $height * $width * 4) !== ($actual = strlen($data))) {
			throw new \InvalidArgumentException("Data should be exactly $expected bytes, got $actual bytes");
		}
	}

	public static function fromLegacy(string $data) : SkinImage
	{
		switch (strlen($data)) {
			case 64 * 32 * 4:
				return new self(32, 64, $data);
			case 64 * 64 * 4:
				return new self(64, 64, $data);
			case 128 * 128 * 4:
				return new self(128, 128, $data);
		}

		throw new \InvalidArgumentException("Unknown size");
	}

	public function getHeight() : int
	{
		return $this->height;
	}

	public function getWidth() : int
	{
		return $this->width;
	}

	public function getData() : string
	{
		return $this->data;
	}
}
