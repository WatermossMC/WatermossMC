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

namespace watermossmc\world\format\io\leveldb;

final class SubChunkVersion
{
	private function __construct()
	{
		//NOOP
	}

	/** Original subchunk format as of MCPE 1.0 */
	public const CLASSIC = 0;
	/** First paletted version, seen in 1.2.13 */
	public const PALETTED_SINGLE = 1;

	//the following are not used by vanilla, but treated the same as version 0 due to a legacy converter which
	//erroneously used the version byte as subchunk height
	public const CLASSIC_BUG_2 = 2;
	public const CLASSIC_BUG_3 = 3;
	public const CLASSIC_BUG_4 = 4;
	public const CLASSIC_BUG_5 = 5;
	public const CLASSIC_BUG_6 = 6;
	public const CLASSIC_BUG_7 = 7;

	/**
	 * Paletted with layers, almost identical to v1, but includes a length prefix and 0 or more storages.
	 * First seen in 1.4 Update Aquatic to support water inside other blocks.
	 */
	public const PALETTED_MULTI = 8;

	/**
	 * Paletted with layers, identical to v8 except for a height byte after the layercount byte.
	 * This seems like a pointless change, but newest versions of the game use it.
	 * First seen in 1.18 for Caves and Cliffs update (and some experimental versions prior to 1.18).
	 */
	public const PALETTED_MULTI_WITH_OFFSET = 9;
}
