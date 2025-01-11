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

namespace watermossmc\block\tile;

use watermossmc\block\utils\BannerPatternLayer;
use watermossmc\block\utils\DyeColor;
use watermossmc\data\bedrock\BannerPatternTypeIdMap;
use watermossmc\data\bedrock\DyeColorIdMap;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\nbt\tag\ListTag;

/**
 * @deprecated
 * @see \watermossmc\block\BaseBanner
 */
class Banner extends Spawnable
{
	public const TAG_BASE = "Base";
	public const TAG_PATTERNS = "Patterns";
	public const TAG_PATTERN_COLOR = "Color";
	public const TAG_PATTERN_NAME = "Pattern";

	private DyeColor $baseColor = DyeColor::BLACK;

	/**
	 * @var BannerPatternLayer[]
	 * @phpstan-var list<BannerPatternLayer>
	 */
	private array $patterns = [];

	public function readSaveData(CompoundTag $nbt) : void
	{
		$colorIdMap = DyeColorIdMap::getInstance();
		if (
			($baseColorTag = $nbt->getTag(self::TAG_BASE)) instanceof IntTag &&
			($baseColor = $colorIdMap->fromInvertedId($baseColorTag->getValue())) !== null
		) {
			$this->baseColor = $baseColor;
		} else {
			$this->baseColor = DyeColor::BLACK; //TODO: this should be an error
		}

		$patternTypeIdMap = BannerPatternTypeIdMap::getInstance();

		$patterns = $nbt->getListTag(self::TAG_PATTERNS);
		if ($patterns !== null) {
			/** @var CompoundTag $pattern */
			foreach ($patterns as $pattern) {
				$patternColor = $colorIdMap->fromInvertedId($pattern->getInt(self::TAG_PATTERN_COLOR)) ?? DyeColor::BLACK; //TODO: missing pattern colour should be an error
				$patternType = $patternTypeIdMap->fromId($pattern->getString(self::TAG_PATTERN_NAME));
				if ($patternType === null) {
					continue; //TODO: this should be an error, but right now we don't have the setup to deal with it
				}
				$this->patterns[] = new BannerPatternLayer($patternType, $patternColor);
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$colorIdMap = DyeColorIdMap::getInstance();
		$patternIdMap = BannerPatternTypeIdMap::getInstance();
		$nbt->setInt(self::TAG_BASE, $colorIdMap->toInvertedId($this->baseColor));
		$patterns = new ListTag();
		foreach ($this->patterns as $pattern) {
			$patterns->push(
				CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $patternIdMap->toId($pattern->getType()))
				->setInt(self::TAG_PATTERN_COLOR, $colorIdMap->toInvertedId($pattern->getColor()))
			);
		}
		$nbt->setTag(self::TAG_PATTERNS, $patterns);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$colorIdMap = DyeColorIdMap::getInstance();
		$patternIdMap = BannerPatternTypeIdMap::getInstance();
		$nbt->setInt(self::TAG_BASE, $colorIdMap->toInvertedId($this->baseColor));
		$patterns = new ListTag();
		foreach ($this->patterns as $pattern) {
			$patterns->push(
				CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $patternIdMap->toId($pattern->getType()))
				->setInt(self::TAG_PATTERN_COLOR, $colorIdMap->toInvertedId($pattern->getColor()))
			);
		}
		$nbt->setTag(self::TAG_PATTERNS, $patterns);
	}

	/**
	 * Returns the color of the banner base.
	 */
	public function getBaseColor() : DyeColor
	{
		return $this->baseColor;
	}

	/**
	 * Sets the color of the banner base.
	 */
	public function setBaseColor(DyeColor $color) : void
	{
		$this->baseColor = $color;
	}

	/**
	 * @return BannerPatternLayer[]
	 * @phpstan-return list<BannerPatternLayer>
	 */
	public function getPatterns() : array
	{
		return $this->patterns;
	}

	/**
	 * @param BannerPatternLayer[] $patterns
	 *
	 * @phpstan-param list<BannerPatternLayer> $patterns
	 */
	public function setPatterns(array $patterns) : void
	{
		$this->patterns = $patterns;
	}

	public function getDefaultName() : string
	{
		return "Banner";
	}
}
