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

namespace watermossmc\nbt;

class ReaderTracker
{
	/** @var int */
	private $maxDepth;
	/** @var int */
	private $currentDepth = 0;

	public function __construct(int $maxDepth)
	{
		$this->maxDepth = $maxDepth;
	}

	/**
	 * @throws NbtDataException if the recursion depth is too deep
	 */
	public function protectDepth(\Closure $execute) : void
	{
		if ($this->maxDepth > 0 && ++$this->currentDepth > $this->maxDepth) {
			throw new NbtDataException("Nesting level too deep: reached max depth of $this->maxDepth tags");
		}
		try {
			$execute();
		} finally {
			--$this->currentDepth;
		}
	}
}
