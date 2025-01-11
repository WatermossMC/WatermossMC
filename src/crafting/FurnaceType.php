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

namespace watermossmc\crafting;

use watermossmc\utils\LegacyEnumShimTrait;
use watermossmc\world\sound\BlastFurnaceSound;
use watermossmc\world\sound\CampfireSound;
use watermossmc\world\sound\FurnaceSound;
use watermossmc\world\sound\SmokerSound;
use watermossmc\world\sound\Sound;

use function spl_object_id;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static FurnaceType BLAST_FURNACE()
 * @method static FurnaceType CAMPFIRE()
 * @method static FurnaceType FURNACE()
 * @method static FurnaceType SMOKER()
 * @method static FurnaceType SOUL_CAMPFIRE()
 *
 * @phpstan-type TMetadata array{0: int, 1: Sound}
 */
enum FurnaceType
{
	use LegacyEnumShimTrait;

	case FURNACE;
	case BLAST_FURNACE;
	case SMOKER;
	case CAMPFIRE;
	case SOUL_CAMPFIRE;

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array
	{
		/** @phpstan-var array<int, TMetadata> $cache */
		static $cache = [];

		return $cache[spl_object_id($this)] ??= match($this) {
			self::FURNACE => [200, new FurnaceSound()],
			self::BLAST_FURNACE => [100, new BlastFurnaceSound()],
			self::SMOKER => [100, new SmokerSound()],
			self::CAMPFIRE, self::SOUL_CAMPFIRE => [600, new CampfireSound()]
		};
	}

	public function getCookDurationTicks() : int
	{
		return $this->getMetadata()[0];
	}

	public function getCookSound() : Sound
	{
		return $this->getMetadata()[1];
	}
}
