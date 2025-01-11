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

namespace watermossmc\plugin;

use watermossmc\utils\LegacyEnumShimTrait;

use function mb_strtolower;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static PluginEnableOrder POSTWORLD()
 * @method static PluginEnableOrder STARTUP()
 */
enum PluginEnableOrder
{
	use LegacyEnumShimTrait;

	case STARTUP;
	case POSTWORLD;

	public static function fromString(string $name) : ?self
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
		return $aliasMap[mb_strtolower($name)] ?? null;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAliases() : array
	{
		return match($this) {
			self::STARTUP => ["startup"],
			self::POSTWORLD => ["postworld"]
		};
	}
}
