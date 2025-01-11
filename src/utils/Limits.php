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

namespace watermossmc\utils;

final class Limits
{
	private function __construct()
	{
		//NOOP
	}

	public const UINT8_MAX = 0xff;
	public const INT8_MIN = -0x7f - 1;
	public const INT8_MAX = 0x7f;

	public const UINT16_MAX = 0xffff;
	public const INT16_MIN = -0x7fff - 1;
	public const INT16_MAX = 0x7fff;

	public const UINT32_MAX = 0xffffffff;
	public const INT32_MIN = -0x7fffffff - 1;
	public const INT32_MAX = 0x7fffffff;

	public const UINT64_MAX = 0xffffffffffffffff;
	public const INT64_MIN = -0x7fffffffffffffff - 1;
	public const INT64_MAX = 0x7fffffffffffffff;
}
