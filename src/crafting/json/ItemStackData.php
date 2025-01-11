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

namespace watermossmc\crafting\json;

final class ItemStackData
{
	/** @required */
	public string $name;

	public int $count;
	public string $block_states;
	public int $meta;
	public string $nbt;
	/** @var string[] */
	public array $can_place_on;
	/** @var string[] */
	public array $can_destroy;

	public function __construct(string $name)
	{
		$this->name = $name;
	}
}
