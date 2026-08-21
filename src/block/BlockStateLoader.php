<?php

declare(strict_types=1);

namespace watermossmc\block;

use RuntimeException;
use watermossmc\nbt\NBT;

final class BlockStateLoader
{
	public static function load(string $file): RuntimeIdMap
	{
		if (!is_file($file)) {
			throw new RuntimeException(
				"Block state data not found: {$file}"
			);
		}

		$data = file_get_contents($file);

		if ($data === false) {
			throw new RuntimeException(
				"Failed to read block state data: {$file}"
			);
		}

		return self::loadString($data);
	}

	public static function loadString(string $data): RuntimeIdMap
	{
		$nbt = NBT::parseMultipleNetwork($data);

		if (!is_array($nbt)) {
			throw new RuntimeException(
				'Invalid canonical block state data'
			);
		}

		$map = new RuntimeIdMap();

		foreach ($nbt as $runtimeId => $entry) {
			if (!is_array($entry)) {
				continue;
			}

			$name = $entry['name'] ?? null;
			$states = $entry['states'] ?? [];

			if (!is_string($name)) {
				continue;
			}

			if (!is_array($states)) {
				$states = [];
			}

			$state = new BlockState(
				$name,
				$states
			);

			$map->register(
				$state,
				(int) $runtimeId
			);
		}

		return $map;
	}
}