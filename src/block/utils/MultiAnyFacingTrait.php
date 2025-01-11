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
