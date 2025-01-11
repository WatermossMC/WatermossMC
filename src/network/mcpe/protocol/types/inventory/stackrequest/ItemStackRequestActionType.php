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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackrequest;

final class ItemStackRequestActionType
{
	private function __construct()
	{
		//NOOP
	}

	public const TAKE = 0;
	public const PLACE = 1;
	public const SWAP = 2;
	public const DROP = 3;
	public const DESTROY = 4;
	public const CRAFTING_CONSUME_INPUT = 5;
	public const CRAFTING_CREATE_SPECIFIC_RESULT = 6;
	public const LAB_TABLE_COMBINE = 9;
	public const BEACON_PAYMENT = 10;
	public const MINE_BLOCK = 11;
	public const CRAFTING_RECIPE = 12;
	public const CRAFTING_RECIPE_AUTO = 13; //recipe book?
	public const CREATIVE_CREATE = 14;
	public const CRAFTING_RECIPE_OPTIONAL = 15; //anvil/cartography table rename
	public const CRAFTING_GRINDSTONE = 16;
	public const CRAFTING_LOOM = 17;
	public const CRAFTING_NON_IMPLEMENTED_DEPRECATED_ASK_TY_LAING = 18;
	public const CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING = 19; //no idea what this is for
}
