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

namespace watermossmc\world\format\io\region;

use function range;

class RegionLocationTableEntry
{
	private int $firstSector;
	/** @phpstan-var positive-int */
	private int $sectorCount;
	private int $timestamp;

	/**
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $firstSector, int $sectorCount, int $timestamp)
	{
		if ($firstSector < 0 || $firstSector >= 2 ** 24) {
			throw new \InvalidArgumentException("Start sector must be positive, got $firstSector");
		}
		$this->firstSector = $firstSector;
		if ($sectorCount < 1) {
			throw new \InvalidArgumentException("Sector count must be positive, got $sectorCount");
		}
		$this->sectorCount = $sectorCount;
		$this->timestamp = $timestamp;
	}

	public function getFirstSector() : int
	{
		return $this->firstSector;
	}

	public function getLastSector() : int
	{
		return $this->firstSector + $this->sectorCount - 1;
	}

	/**
	 * Returns an array of sector offsets reserved by this chunk.
	 * @return int[]
	 */
	public function getUsedSectors() : array
	{
		return range($this->getFirstSector(), $this->getLastSector());
	}

	/**
	 * @phpstan-return positive-int
	 */
	public function getSectorCount() : int
	{
		return $this->sectorCount;
	}

	public function getTimestamp() : int
	{
		return $this->timestamp;
	}

	public function overlaps(RegionLocationTableEntry $other) : bool
	{
		$overlapCheck = static function (RegionLocationTableEntry $entry1, RegionLocationTableEntry $entry2) : bool {
			$entry1Last = $entry1->getLastSector();
			$entry2Last = $entry2->getLastSector();

			return (
				($entry2->firstSector >= $entry1->firstSector && $entry2->firstSector <= $entry1Last) ||
				($entry2Last >= $entry1->firstSector && $entry2Last <= $entry1Last)
			);
		};
		return $overlapCheck($this, $other) || $overlapCheck($other, $this);
	}
}
