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

namespace watermossmc\network\mcpe\protocol\types\entity;

final class EntityMetadataTypes
{
	private function __construct()
	{
		//NOOP
	}

	public const BYTE = 0;
	public const SHORT = 1;
	public const INT = 2;
	public const FLOAT = 3;
	public const STRING = 4;
	public const COMPOUND_TAG = 5;
	public const POS = 6;
	public const LONG = 7;
	public const VECTOR3F = 8;
}
