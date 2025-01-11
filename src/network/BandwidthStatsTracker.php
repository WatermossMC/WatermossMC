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

namespace watermossmc\network;

use function array_fill;
use function array_sum;
use function count;

final class BandwidthStatsTracker
{
	/** @var int[] */
	private array $history;
	private int $nextHistoryIndex = 0;
	private int $bytesSinceLastRotation = 0;
	private int $totalBytes = 0;

	/** @phpstan-param positive-int $historySize */
	public function __construct(int $historySize)
	{
		$this->history = array_fill(0, $historySize, 0);
	}

	public function add(int $bytes) : void
	{
		$this->totalBytes += $bytes;
		$this->bytesSinceLastRotation += $bytes;
	}

	public function getTotalBytes() : int
	{
		return $this->totalBytes;
	}

	/**
	 * Adds the bytes tracked since the last rotation to the history array, overwriting an old entry.
	 * This should be called on a regular interval that you want to collect average measurements over
	 * (e.g. if you want bytes per second, call this every second).
	 */
	public function rotateHistory() : void
	{
		$this->history[$this->nextHistoryIndex] = $this->bytesSinceLastRotation;
		$this->bytesSinceLastRotation = 0;
		$this->nextHistoryIndex = ($this->nextHistoryIndex + 1) % count($this->history);
	}

	/**
	 * Returns the average of all the tracked history values.
	 */
	public function getAverageBytes() : float
	{
		return array_sum($this->history) / count($this->history);
	}

	public function resetHistory() : void
	{
		$this->history = array_fill(0, count($this->history), 0);
	}
}
