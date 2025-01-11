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

namespace watermossmc\world\light;

final class LightPropagationContext
{
	/** @phpstan-var \SplQueue<array{int, int, int}> */
	public \SplQueue $spreadQueue;
	/**
	 * @var int[]|true[]
	 * @phpstan-var array<int, int|true>
	 */
	public array $spreadVisited = [];

	/** @phpstan-var \SplQueue<array{int, int, int, int}> */
	public \SplQueue $removalQueue;
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	public array $removalVisited = [];

	public function __construct()
	{
		$this->removalQueue = new \SplQueue();
		$this->spreadQueue = new \SplQueue();
	}
}
