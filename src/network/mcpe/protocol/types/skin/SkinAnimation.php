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

class SkinAnimation
{
	public const TYPE_HEAD = 1;
	public const TYPE_BODY_32 = 2;
	public const TYPE_BODY_64 = 3;

	public const EXPRESSION_LINEAR = 0; //???
	public const EXPRESSION_BLINKING = 1;

	public function __construct(
		private SkinImage $image,
		private int $type,
		private float $frames,
		private int $expressionType
	) {
	}

	/**
	 * Image of the animation.
	 */
	public function getImage() : SkinImage
	{
		return $this->image;
	}

	/**
	 * The type of animation you are applying.
	 */
	public function getType() : int
	{
		return $this->type;
	}

	/**
	 * The total amount of frames in an animation.
	 */
	public function getFrames() : float
	{
		return $this->frames;
	}

	public function getExpressionType() : int
	{
		return $this->expressionType;
	}
}
