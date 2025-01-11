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

final class GlobalLogger
{
	private function __construct()
	{
		//NOOP
	}

	private static ?Logger $logger = null;

	public static function get() : Logger
	{
		if (self::$logger === null) {
			self::$logger = new SimpleLogger();
		}
		return self::$logger;
	}

	public static function set(Logger $logger) : void
	{
		self::$logger = $logger;
	}
}
