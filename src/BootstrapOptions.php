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

namespace watermossmc;

/**
 * Constants for all the command-line options that WatermossMC supports.
 * Other options not listed here can be used to override server.properties and watermossmc.yml values temporarily.
 *
 * @internal
 */
final class BootstrapOptions
{
	private function __construct()
	{
		//NOOP
	}

	/** Disables the setup wizard on first startup */
	public const NO_WIZARD = "no-wizard";
	/** Force-disables console text colour and formatting */
	public const DISABLE_ANSI = "disable-ansi";
	/** Force-enables console text colour and formatting */
	public const ENABLE_ANSI = "enable-ansi";
	/** Path to look in for plugins */
	public const PLUGINS = "plugins";
	/** Path to store and load server data */
	public const DATA = "data";
	/** Shows basic server version information and exits */
	public const VERSION = "version";
	/** Disables writing logs to server.log */
	public const NO_LOG_FILE = "no-log-file";
}
