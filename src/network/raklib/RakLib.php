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

namespace watermossmc\network\raklib;

abstract class RakLib
{
	/**
	 * Default vanilla RakNet protocol version that this library implements. Things using RakLib can override this
	 * protocol version with something different.
	 */
	public const DEFAULT_PROTOCOL_VERSION = 6;

	/** Regular RakNet uses 10 by default. MCPE uses 20. Configure this value as appropriate. */
	public static int $SYSTEM_ADDRESS_COUNT = 20;
}
