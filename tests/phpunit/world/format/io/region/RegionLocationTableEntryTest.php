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

use PHPUnit\Framework\TestCase;

use function sprintf;

class RegionLocationTableEntryTest extends TestCase
{
	/**
	 * @phpstan-return \Generator<int, array{RegionLocationTableEntry, RegionLocationTableEntry, bool}, void, void>
	 */
	public static function overlapDataProvider() : \Generator
	{
		yield [new RegionLocationTableEntry(2, 1, 0), new RegionLocationTableEntry(2, 1, 0), true];
		yield [new RegionLocationTableEntry(2, 1, 0), new RegionLocationTableEntry(3, 1, 0), false];
		yield [new RegionLocationTableEntry(2, 2, 0), new RegionLocationTableEntry(3, 2, 0), true];
		yield [new RegionLocationTableEntry(2, 2, 0), new RegionLocationTableEntry(4, 2, 0), false];
		yield [new RegionLocationTableEntry(2, 2, 0), new RegionLocationTableEntry(2, 1, 0), true];
		yield [new RegionLocationTableEntry(2, 4, 0), new RegionLocationTableEntry(3, 1, 0), true];
	}

	/**
	 * @dataProvider overlapDataProvider
	 */
	public function testOverlap(RegionLocationTableEntry $entry1, RegionLocationTableEntry $entry2, bool $overlaps) : void
	{
		$stringify = function (RegionLocationTableEntry $entry) : string {
			return sprintf("entry first=%d last=%d size=%d", $entry->getFirstSector(), $entry->getLastSector(), $entry->getSectorCount());
		};
		self::assertSame($overlaps, $entry1->overlaps($entry2), $stringify($entry1) . " expected to " . ($overlaps ? "overlap" : "not overlap") . " with " . $stringify($entry2));
		self::assertSame($overlaps, $entry2->overlaps($entry1), $stringify($entry2) . " expected to " . ($overlaps ? "overlap" : "not overlap") . " with " . $stringify($entry1));
	}
}
