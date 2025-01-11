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

namespace watermossmc\item;

use watermossmc\block\tile\Banner as TileBanner;
use watermossmc\block\utils\BannerPatternLayer;
use watermossmc\block\utils\DyeColor;
use watermossmc\data\bedrock\BannerPatternTypeIdMap;
use watermossmc\data\bedrock\DyeColorIdMap;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\nbt\NBT;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\ListTag;

use function count;

class Banner extends ItemBlockWallOrFloor
{
	public const TAG_PATTERNS = TileBanner::TAG_PATTERNS;
	public const TAG_PATTERN_COLOR = TileBanner::TAG_PATTERN_COLOR;
	public const TAG_PATTERN_NAME = TileBanner::TAG_PATTERN_NAME;

	private DyeColor $color = DyeColor::BLACK;

	/**
	 * @var BannerPatternLayer[]
	 * @phpstan-var list<BannerPatternLayer>
	 */
	private array $patterns = [];

	public function getColor() : DyeColor
	{
		return $this->color;
	}

	/** @return $this */
	public function setColor(DyeColor $color) : self
	{
		$this->color = $color;
		return $this;
	}

	protected function describeState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->color);
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
	 *
	 * @return $this
	 */
	public function setPatterns(array $patterns) : self
	{
		$this->patterns = $patterns;
		return $this;
	}

	public function getFuelTime() : int
	{
		return 300;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void
	{
		parent::deserializeCompoundTag($tag);

		$this->patterns = [];

		$colorIdMap = DyeColorIdMap::getInstance();
		$patternIdMap = BannerPatternTypeIdMap::getInstance();
		$patterns = $tag->getListTag(self::TAG_PATTERNS);
		if ($patterns !== null && $patterns->getTagType() === NBT::TAG_Compound) {
			/** @var CompoundTag $t */
			foreach ($patterns as $t) {
				$patternColor = $colorIdMap->fromInvertedId($t->getInt(self::TAG_PATTERN_COLOR)) ?? DyeColor::BLACK; //TODO: missing pattern colour should be an error
				$patternType = $patternIdMap->fromId($t->getString(self::TAG_PATTERN_NAME));
				if ($patternType === null) {
					continue; //TODO: this should be an error
				}
				$this->patterns[] = new BannerPatternLayer($patternType, $patternColor);
			}
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void
	{
		parent::serializeCompoundTag($tag);

		if (count($this->patterns) > 0) {
			$patterns = new ListTag();
			$colorIdMap = DyeColorIdMap::getInstance();
			$patternIdMap = BannerPatternTypeIdMap::getInstance();
			foreach ($this->patterns as $pattern) {
				$patterns->push(
					CompoundTag::create()
					->setString(self::TAG_PATTERN_NAME, $patternIdMap->toId($pattern->getType()))
					->setInt(self::TAG_PATTERN_COLOR, $colorIdMap->toInvertedId($pattern->getColor()))
				);
			}

			$tag->setTag(self::TAG_PATTERNS, $patterns);
		} else {
			$tag->removeTag(self::TAG_PATTERNS);
		}
	}
}
