<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\block\utils;

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\math\Facing;

/**
 * Used by blocks that can have multiple target faces in the area of one solid block, such as covering three sides of a corner.
 */
trait MultiAnyFacingTrait
{
	/** @var int[] */
	protected array $faces = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->facingFlags($this->faces);
	}

	/** @return int[] */
	public function getFaces() : array
	{
		return $this->faces;
	}

	public function hasFace(int $face) : bool
	{
		return isset($this->faces[$face]);
	}

	/**
	 * @param int[] $faces
	 * @return $this
	 */
	public function setFaces(array $faces) : self
	{
		$uniqueFaces = [];
		foreach ($faces as $face) {
			Facing::validate($face);
			$uniqueFaces[$face] = $face;
		}
		$this->faces = $uniqueFaces;
		return $this;
	}

	/** @return $this */
	public function setFace(int $face, bool $value) : self
	{
		Facing::validate($face);
		if ($value) {
			$this->faces[$face] = $face;
		} else {
			unset($this->faces[$face]);
		}
		return $this;
	}
}
