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

namespace watermossmc\network\mcpe\convert;

use PHPUnit\Framework\TestCase;
use watermossmc\block\RuntimeBlockStateRegistry;

class BlockTranslatorTest extends TestCase
{
	/**
	 * @doesNotPerformAssertions
	 */
	public function testAllBlockStatesSerialize() : void
	{
		$blockTranslator = TypeConverter::getInstance()->getBlockTranslator();
		foreach (RuntimeBlockStateRegistry::getInstance()->getAllKnownStates() as $state) {
			$blockTranslator->internalIdToNetworkId($state->getStateId());
		}
	}
}
