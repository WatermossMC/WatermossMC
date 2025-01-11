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

namespace watermossmc\world\generator;

use function exp;

final class Gaussian
{
	/** @var float[][] */
	public array $kernel = [];

	public function __construct(public int $smoothSize)
	{
		$bellSize = 1 / $this->smoothSize;
		$bellHeight = 2 * $this->smoothSize;

		for ($sx = -$this->smoothSize; $sx <= $this->smoothSize; ++$sx) {
			$this->kernel[$sx + $this->smoothSize] = [];

			for ($sz = -$this->smoothSize; $sz <= $this->smoothSize; ++$sz) {
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				$this->kernel[$sx + $this->smoothSize][$sz + $this->smoothSize] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}
}
