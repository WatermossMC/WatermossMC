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

namespace watermossmc\permission;

use watermossmc\utils\Utils;

use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function is_resource;
use function strtolower;
use function trim;

class BanList
{
	/**
	 * @var BanEntry[]
	 * @phpstan-var array<string, BanEntry>
	 */
	private array $list = [];

	private bool $enabled = true;

	public function __construct(
		private string $file
	) {
	}

	public function isEnabled() : bool
	{
		return $this->enabled;
	}

	public function setEnabled(bool $flag) : void
	{
		$this->enabled = $flag;
	}

	public function getEntry(string $name) : ?BanEntry
	{
		$this->removeExpired();

		return $this->list[strtolower($name)] ?? null;
	}

	/**
	 * @return BanEntry[]
	 */
	public function getEntries() : array
	{
		$this->removeExpired();

		return $this->list;
	}

	public function isBanned(string $name) : bool
	{
		$name = strtolower($name);
		if (!$this->isEnabled()) {
			return false;
		} else {
			$this->removeExpired();

			return isset($this->list[$name]);
		}
	}

	public function add(BanEntry $entry) : void
	{
		$this->list[$entry->getName()] = $entry;
		$this->save();
	}

	public function addBan(string $target, ?string $reason = null, ?\DateTime $expires = null, ?string $source = null) : BanEntry
	{
		$entry = new BanEntry($target);
		$entry->setSource($source ?? $entry->getSource());
		$entry->setExpires($expires);
		$entry->setReason($reason ?? $entry->getReason());

		$this->list[$entry->getName()] = $entry;
		$this->save();

		return $entry;
	}

	public function remove(string $name) : void
	{
		$name = strtolower($name);
		if (isset($this->list[$name])) {
			unset($this->list[$name]);
			$this->save();
		}
	}

	public function removeExpired() : void
	{
		foreach (Utils::promoteKeys($this->list) as $name => $entry) {
			if ($entry->hasExpired()) {
				unset($this->list[$name]);
			}
		}
	}

	public function load() : void
	{
		$this->list = [];
		$fp = @fopen($this->file, "r");
		if (is_resource($fp)) {
			while (($line = fgets($fp)) !== false) {
				if ($line[0] !== "#") {
					try {
						$entry = BanEntry::fromString($line);
						if ($entry !== null) {
							$this->list[$entry->getName()] = $entry;
						}
					} catch (\RuntimeException $e) {
						$logger = \GlobalLogger::get();
						$logger->critical("Failed to parse ban entry from string \"" . trim($line) . "\": " . $e->getMessage());
					}

				}
			}
			fclose($fp);
		} else {
			\GlobalLogger::get()->error("Could not load ban list");
		}
	}

	public function save(bool $writeHeader = true) : void
	{
		$this->removeExpired();
		$fp = @fopen($this->file, "w");
		if (is_resource($fp)) {
			if ($writeHeader) {
				fwrite($fp, "# victim name | ban date | banned by | banned until | reason\n\n");
			}

			foreach ($this->list as $entry) {
				fwrite($fp, $entry->getString() . "\n");
			}
			fclose($fp);
		} else {
			\GlobalLogger::get()->error("Could not save ban list");
		}
	}
}
