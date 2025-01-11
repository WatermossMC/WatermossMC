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

use watermossmc\color\Color;

class MapDecoration
{
	public function __construct(
		private int $icon,
		private int $rotation,
		private int $xOffset,
		private int $yOffset,
		private string $label,
		private Color $color
	) {
	}

	public function getIcon() : int
	{
		return $this->icon;
	}

	public function getRotation() : int
	{
		return $this->rotation;
	}

	public function getXOffset() : int
	{
		return $this->xOffset;
	}

	public function getYOffset() : int
	{
		return $this->yOffset;
	}

	public function getLabel() : string
	{
		return $this->label;
	}

	public function getColor() : Color
	{
		return $this->color;
	}
}
