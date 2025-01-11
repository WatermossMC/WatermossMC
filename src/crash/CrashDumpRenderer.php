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

use watermossmc\utils\Utils;
use watermossmc\utils\VersionString;

use function count;
use function date;
use function fwrite;
use function implode;

use const PHP_EOL;

final class CrashDumpRenderer
{
	/**
	 * @param resource $fp
	 */
	public function __construct(private $fp, private CrashDumpData $data)
	{

	}

	public function renderHumanReadable() : void
	{
		$this->addLine($this->data->general->name . " Crash Dump " . date("D M j H:i:s T Y", (int) $this->data->time));
		$this->addLine();

		$version = new VersionString($this->data->general->base_version, $this->data->general->is_dev, $this->data->general->build);
		$this->addLine($this->data->general->name . " version: " . $version->getFullVersion(true) . " [Protocol " . $this->data->general->protocol . "]");
		$this->addLine("Git commit: " . $this->data->general->git);
		$this->addLine("PHP version: " . $this->data->general->php);
		$this->addLine("OS: " . $this->data->general->php_os . ", " . $this->data->general->os);

		if ($this->data->plugin_involvement !== CrashDump::PLUGIN_INVOLVEMENT_NONE) {
			$this->addLine();
			$this->addLine(match($this->data->plugin_involvement) {
				CrashDump::PLUGIN_INVOLVEMENT_DIRECT => "THIS CRASH WAS CAUSED BY A PLUGIN",
				CrashDump::PLUGIN_INVOLVEMENT_INDIRECT => "A PLUGIN WAS INVOLVED IN THIS CRASH",
				default => "Unknown plugin involvement!"
			});
		}
		if ($this->data->plugin !== "") {
			$this->addLine("BAD PLUGIN: " . $this->data->plugin);
		}

		$this->addLine();

		$this->addLine("Thread: " . $this->data->thread);
		$this->addLine("Error: " . $this->data->error["message"]);
		$this->addLine("File: " . $this->data->error["file"]);
		$this->addLine("Line: " . $this->data->error["line"]);
		$this->addLine("Type: " . $this->data->error["type"]);
		$this->addLine("Backtrace:");
		foreach ($this->data->trace as $line) {
			$this->addLine($line);
		}

		$this->addLine();
		$this->addLine("Code:");

		foreach ($this->data->code as $lineNumber => $line) {
			$this->addLine("[$lineNumber] $line");
		}

		if (count($this->data->plugins) > 0) {
			$this->addLine();
			$this->addLine("Loaded plugins:");
			foreach ($this->data->plugins as $p) {
				$this->addLine($p->name . " " . $p->version . " by " . implode(", ", $p->authors) . " for API(s) " . implode(", ", $p->api));
			}
		}

		$this->addLine();
		$this->addLine("uname -a: " . $this->data->general->uname);
		$this->addLine("Zend version: " . $this->data->general->zend);
		$this->addLine("Composer libraries: ");
		foreach (Utils::stringifyKeys($this->data->general->composer_libraries) as $library => $libraryVersion) {
			$this->addLine("- $library $libraryVersion");
		}
	}

	public function addLine(string $line = "") : void
	{
		fwrite($this->fp, $line . PHP_EOL);
	}
}
