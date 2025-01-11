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

namespace watermossmc\data\bedrock\block\upgrade;

use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\nbt\tag\StringTag;

use function ksort;

use const SORT_STRING;

final class BlockStateUpgradeSchemaFlattenInfo
{
	/**
	 * @param string[] $flattenedValueRemaps
	 * @phpstan-param array<string, string> $flattenedValueRemaps
	 * @phpstan-param ?class-string<ByteTag|IntTag|StringTag> $flattenedPropertyType
	 */
	public function __construct(
		public string $prefix,
		public string $flattenedProperty,
		public string $suffix,
		public array $flattenedValueRemaps,
		public ?string $flattenedPropertyType = null
	) {
		ksort($this->flattenedValueRemaps, SORT_STRING);
	}

	public function equals(self $that) : bool
	{
		return $this->prefix === $that->prefix &&
			$this->flattenedProperty === $that->flattenedProperty &&
			$this->suffix === $that->suffix &&
			$this->flattenedValueRemaps === $that->flattenedValueRemaps &&
			$this->flattenedPropertyType === $that->flattenedPropertyType;
	}
}
