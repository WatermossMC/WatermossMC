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

namespace watermossmc\data\bedrock\block\upgrade\model;

use function count;

final class BlockStateUpgradeSchemaModelFlattenInfo implements \JsonSerializable
{
	/** @required */
	public string $prefix;
	/** @required */
	public string $flattenedProperty;
	public ?string $flattenedPropertyType = null;
	/** @required */
	public string $suffix;
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $flattenedValueRemaps;

	/**
	 * @param string[] $flattenedValueRemaps
	 * @phpstan-param array<string, string> $flattenedValueRemaps
	 */
	public function __construct(string $prefix, string $flattenedProperty, string $suffix, array $flattenedValueRemaps, ?string $flattenedPropertyType = null)
	{
		$this->prefix = $prefix;
		$this->flattenedProperty = $flattenedProperty;
		$this->suffix = $suffix;
		$this->flattenedValueRemaps = $flattenedValueRemaps;
		$this->flattenedPropertyType = $flattenedPropertyType;
	}

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize() : array
	{
		$result = (array) $this;
		if (count($this->flattenedValueRemaps) === 0) {
			unset($result["flattenedValueRemaps"]);
		}
		if ($this->flattenedPropertyType === null) {
			unset($result["flattenedPropertyType"]);
		}
		return $result;
	}
}
