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

namespace watermossmc\block;

use function array_fill_keys;
use function array_keys;

final class BlockTypeInfo
{
	/**
	 * @var true[]
	 * @phpstan-var array<string, true>
	 */
	private array $typeTags;

	/**
	 * @param string[] $typeTags
	 * @param string[] $enchantmentTags
	 */
	public function __construct(
		private BlockBreakInfo $breakInfo,
		array $typeTags = [],
		private array $enchantmentTags = []
	) {
		$this->typeTags = array_fill_keys($typeTags, true);
	}

	public function getBreakInfo() : BlockBreakInfo
	{
		return $this->breakInfo;
	}

	/** @return string[] */
	public function getTypeTags() : array
	{
		return array_keys($this->typeTags);
	}

	public function hasTypeTag(string $tag) : bool
	{
		return isset($this->typeTags[$tag]);
	}

	/**
	 * Returns tags that represent the type of item being enchanted and are used to determine
	 * what enchantments can be applied to the item of this block during in-game enchanting (enchanting table, anvil, fishing, etc.).
	 * @see ItemEnchantmentTags
	 * @see ItemEnchantmentTagRegistry
	 * @see AvailableEnchantmentRegistry
	 *
	 * @return string[]
	 */
	public function getEnchantmentTags() : array
	{
		return $this->enchantmentTags;
	}
}
