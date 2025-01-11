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

namespace watermossmc\world\utils;

final class SubChunkExplorerStatus
{
	private function __construct()
	{
		//NOOP
	}

	/** We encountered terrain not accessible by the current terrain provider */
	public const INVALID = 0;
	/** We remained inside the same (sub)chunk */
	public const OK = 1;
	/** We moved to a different (sub)chunk */
	public const MOVED = 2;
}
