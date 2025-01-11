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

namespace watermossmc\network\mcpe\protocol\types\resourcepacks;

final class ResourcePackType
{
	private function __construct()
	{
		//NOOP
	}

	public const INVALID = 0;
	public const ADDON = 1;
	public const CACHED = 2;
	public const COPY_PROTECTED = 3;
	public const BEHAVIORS = 4;
	public const PERSONA_PIECE = 5;
	public const RESOURCES = 6;
	public const SKINS = 7;
	public const WORLD_TEMPLATE = 8;
}
