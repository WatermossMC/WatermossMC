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

namespace watermossmc\crash;

final class CrashDumpData implements \JsonSerializable
{
	public int $format_version;

	public float $time;

	public float $uptime;

	/** @var mixed[] */
	public array $lastError = [];

	/** @var mixed[] */
	public array $error;

	public string $thread;

	public string $plugin_involvement;

	public string $plugin = "";

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	public array $code = [];

	/** @var string[] */
	public array $trace;

	/**
	 * @var CrashDumpDataPluginEntry[]
	 * @phpstan-var array<string, CrashDumpDataPluginEntry>
	 */
	public array $plugins = [];

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	public array $parameters = [];

	public string $serverDotProperties = "";

	public string $watermossmcDotYml = "";

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $extensions = [];

	public ?int $jit_mode = null;

	public string $phpinfo = "";

	public CrashDumpDataGeneral $general;

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize() : array
	{
		$result = (array) $this;
		unset($result["serverDotProperties"]);
		unset($result["watermossmcDotYml"]);
		$result["watermossmc.yml"] = $this->watermossmcDotYml;
		$result["server.properties"] = $this->serverDotProperties;
		return $result;
	}
}
