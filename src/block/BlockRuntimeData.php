<?php

declare(strict_types=1);

namespace watermossmc\block;

final class BlockRuntimeData
{
	private static ?RuntimeIdMap $runtimeIdMap = null;

	private static ?BlockRuntimeIdConverter $converter = null;

	public static function init(string $canonicalBlockStates): void
	{
		self::$runtimeIdMap =
			BlockStateLoader::load(
				$canonicalBlockStates
			);

		self::$converter =
			new BlockRuntimeIdConverter(
				self::$runtimeIdMap
			);
	}

	public static function getRuntimeIdMap(): RuntimeIdMap
	{
		return self::$runtimeIdMap
			?? throw new \RuntimeException(
				'Block runtime data has not been initialized'
			);
	}

	public static function getConverter(): BlockRuntimeIdConverter
	{
		return self::$converter
			?? throw new \RuntimeException(
				'Block runtime data has not been initialized'
			);
	}
}