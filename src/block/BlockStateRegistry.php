<?php

declare(strict_types=1);

namespace watermossmc\block;

use RuntimeException;

final class BlockStateRegistry
{
	private static ?RuntimeIdMap $runtimeIdMap = null;

	public static function init(): void
	{
		self::$runtimeIdMap = new RuntimeIdMap();
	}

	public static function getRuntimeIdMap(): RuntimeIdMap
	{
		if (self::$runtimeIdMap === null) {
			throw new RuntimeException(
				'BlockStateRegistry has not been initialized'
			);
		}

		return self::$runtimeIdMap;
	}
}