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

namespace watermossmc\player;

use watermossmc\lang\KnownTranslationFactory;
use watermossmc\lang\Translatable;
use watermossmc\utils\LegacyEnumShimTrait;

use function mb_strtolower;
use function spl_object_id;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static GameMode ADVENTURE()
 * @method static GameMode CREATIVE()
 * @method static GameMode SPECTATOR()
 * @method static GameMode SURVIVAL()
 *
 * @phpstan-type TMetadata array{0: string, 1: Translatable, 2: list<string>}
 */
enum GameMode
{
	use LegacyEnumShimTrait;

	case SURVIVAL;
	case CREATIVE;
	case ADVENTURE;
	case SPECTATOR;

	public static function fromString(string $str) : ?self
	{
		/**
		 * @var self[]|null $aliasMap
		 * @phpstan-var array<string, self>|null $aliasMap
		 */
		static $aliasMap = null;

		if ($aliasMap === null) {
			$aliasMap = [];
			foreach (self::cases() as $case) {
				foreach ($case->getAliases() as $alias) {
					$aliasMap[$alias] = $case;
				}
			}
		}

		return $aliasMap[mb_strtolower($str)] ?? null;
	}

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array
	{
		/** @phpstan-var array<int, TMetadata> $cache */
		static $cache = [];

		return $cache[spl_object_id($this)] ??= match($this) {
			self::SURVIVAL => ["Survival", KnownTranslationFactory::gameMode_survival(), ["survival", "s", "0"]],
			self::CREATIVE => ["Creative", KnownTranslationFactory::gameMode_creative(), ["creative", "c", "1"]],
			self::ADVENTURE => ["Adventure", KnownTranslationFactory::gameMode_adventure(), ["adventure", "a", "2"]],
			self::SPECTATOR => ["Spectator", KnownTranslationFactory::gameMode_spectator(), ["spectator", "v", "view", "3"]]
		};
	}

	public function getEnglishName() : string
	{
		return $this->getMetadata()[0];
	}

	public function getTranslatableName() : Translatable
	{
		return $this->getMetadata()[1];
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array
	{
		return $this->getMetadata()[2];
	}

	//TODO: ability sets per gamemode
}
