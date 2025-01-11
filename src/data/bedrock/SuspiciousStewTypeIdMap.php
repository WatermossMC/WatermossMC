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

namespace watermossmc\data\bedrock;

use watermossmc\item\SuspiciousStewType;
use watermossmc\utils\SingletonTrait;

final class SuspiciousStewTypeIdMap
{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<SuspiciousStewType> */
	use IntSaveIdMapTrait;

	private function __construct()
	{
		foreach (SuspiciousStewType::cases() as $case) {
			$this->register(match($case) {
				SuspiciousStewType::POPPY => SuspiciousStewTypeIds::POPPY,
				SuspiciousStewType::CORNFLOWER => SuspiciousStewTypeIds::CORNFLOWER,
				SuspiciousStewType::TULIP => SuspiciousStewTypeIds::TULIP,
				SuspiciousStewType::AZURE_BLUET => SuspiciousStewTypeIds::AZURE_BLUET,
				SuspiciousStewType::LILY_OF_THE_VALLEY => SuspiciousStewTypeIds::LILY_OF_THE_VALLEY,
				SuspiciousStewType::DANDELION => SuspiciousStewTypeIds::DANDELION,
				SuspiciousStewType::BLUE_ORCHID => SuspiciousStewTypeIds::BLUE_ORCHID,
				SuspiciousStewType::ALLIUM => SuspiciousStewTypeIds::ALLIUM,
				SuspiciousStewType::OXEYE_DAISY => SuspiciousStewTypeIds::OXEYE_DAISY,
				SuspiciousStewType::WITHER_ROSE => SuspiciousStewTypeIds::WITHER_ROSE,
			}, $case);
		}

	}
}
